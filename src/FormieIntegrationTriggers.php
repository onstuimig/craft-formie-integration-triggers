<?php

namespace onstuimig\formieintegrationtriggers;

use Craft;
use craft\base\Plugin;
use craft\helpers\ArrayHelper;
use onstuimig\formieintegrationtriggers\interfaces\EventTriggersInterface;
use onstuimig\formieintegrationtriggers\services\Triggers;
use verbb\formie\base\Integration;
use verbb\formie\events\IntegrationFormSettingsEvent;
use verbb\formie\events\TriggerIntegrationEvent;
use verbb\formie\Formie;
use verbb\formie\models\IntegrationField;
use verbb\formie\records\Submission as SubmissionRecord;
use verbb\formie\services\Submissions;
use yii\base\Event;

/**
 * Formie Integration Triggers plugin
 *
 * @method static FormieIntegrationTriggers getInstance()
 * @author Onstuimig
 * @copyright Onstuimig
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read Triggers $triggers
 */
class FormieIntegrationTriggers extends Plugin
{
	public string $schemaVersion = '1.0.0';

	public static function config(): array
	{
		return [
			'components' => ['triggers' => Triggers::class],
		];
	}

	public function init(): void
	{
		parent::init();

		$this->attachEventHandlers();

		// Any code that creates an element query or loads Twig should be deferred until
		// after Craft is fully initialized, to avoid conflicts with other plugins/modules
		Craft::$app->onInit(function() {
			$this->initTriggers();
		});
	}

	private function attachEventHandlers(): void
	{
		Event::on(
			Submissions::class,
			Submissions::EVENT_BEFORE_TRIGGER_INTEGRATION,
			function(TriggerIntegrationEvent $event) {
				$integration = $event->integration;

				if (!$integration || !($integration instanceof EventTriggersInterface)) {
					return;
				}

				// If the integration is configured to trigger on events
				// and we are not in the context of an event,
				// stop sending the integration payload
				if (
					!empty($integration->getTriggerOnEvents()) &&
					!isset($integration->context['integrationEvent'])
				) {

					// Save the integration context to the submission snapshot
					$context = $integration->context;
					$submission = $event->submission;
					if ($submission && !empty($context)) {
						$event->submission->snapshot['integrationContext'] = $context;

						// Save the submission
						$submissionRecord = SubmissionRecord::findOne($submission->id);
						if ($submissionRecord) {
							$submissionRecord->setAttribute('snapshot', $submission->snapshot);
							$submissionRecord->save(false);
						}
					}

					// Stop running the integration
					// and prevent other events from running for this integration
					$event->isValid = false;
					$event->handled = true;
				}
			},
			null,
			false
		);

		Event::on(
			Integration::class,
			Integration::EVENT_AFTER_FETCH_FORM_SETTINGS,
			function(IntegrationFormSettingsEvent $event) {
				$integration = $event->integration;

				if (!$integration || !($integration instanceof EventTriggersInterface)) {
					return;
				}

				// If the integration is no configured to trigger on events continue as normal
				if (empty($integration->getTriggerOnEvents())) {
					return;
				}

				// Collect all IntegrationFields
				$fields = self::getIntegrationFields($event->settings);

				// Add Trigger Options to every field
				$integration->addFieldTriggerContextOptions($fields);
			}
		);
	}

	private static function getIntegrationFields(\Traversable|array|null $settings): array
	{
		$fields = [];
		
		if (!$settings) {
			return $fields;
		}

		foreach ($settings as $setting) {
			if ($setting instanceof IntegrationField) {
				$fields[] = $setting;
			} elseif (is_array($setting) || $setting instanceof \Traversable) {
				$fields = array_merge($fields, self::getIntegrationFields($setting));
			}
		}

		return $fields;
	}

	private function initTriggers(): void
	{
		$forms = Formie::getInstance()->getForms()->getAllForms();

		$allIntegrations = Formie::getInstance()->getIntegrations()->getAllIntegrations();
		
		/** @var Integration[] $integrations */
		$integrations = [];

		foreach ($allIntegrations as $integration) {
			if ($integration instanceof EventTriggersInterface) {
				$integrations[] = $integration;
			}
		}

		foreach ($forms as $form) {
			$formIntegrationSettings = $form->settings->integrations ?? [];
			$enabledFormSettings = ArrayHelper::where($formIntegrationSettings, 'enabled', true);

			foreach ($integrations as $integration) {
				if (empty($enabledFormSettings[$integration->handle])) {
					continue;
				}
				if (!$integration->getEnabled()) {
					continue;
				}
				
				$integrationFormSettings = $enabledFormSettings[$integration->handle];

				// Populate the settings
				$integration->setAttributes($integrationFormSettings, false);

				if (empty($integration->getTriggerOnEvents())) {
					continue;
				}

				foreach ($integration->getTriggerOnEvents() as $triggerEventClass) {
					$trigger = $this->triggers->getTrigger($triggerEventClass);

					if ($trigger) {
						$trigger->init($integration);
					}
				}
			}
		}
	}
}
