<?php

namespace onstuimig\formieintegrationtriggers\base;

use Craft;
use craft\helpers\Queue;
use verbb\formie\base\Integration;
use verbb\formie\elements\Submission;
use verbb\formie\Formie;
use verbb\formie\jobs\TriggerIntegration;

abstract class EventTrigger
{
	protected ?Integration $integration = null;

	abstract public function displayName(): string;
	// abstract public function getHandle(): string;

	abstract public function setup(): void;

	public function init(Integration $integration): void
	{
		$this->integration = $integration;

		$this->setup();
	}

	public function getFields(): array
	{
		return [];
	}

	protected function sendPayload(Submission $submission, ?array $fieldData = []): void
	{
		/** @var FormieSettings $settings */
		$settings = Formie::$plugin->getSettings();
		
		if (!$this->integration->supportsPayloadSending()) {
			return;
		}

		// Allow integrations to add extra data before running
		if(Craft::$app->getRequest()->isWebRequest) {
			// TODO: Set context when first triggering the integration, and save it to snapshot
			$this->integration->populateContext();
		}

		$this->integration->context['integrationEvent'] = $fieldData;

		if ($settings->useQueueForIntegrations) {
			Queue::push(new TriggerIntegration([
				'submissionId' => $submission->id,
				'integration' => $this->integration,
			]), $settings->queuePriority);
		} else {
			Formie::getInstance()->getSubmissions()->sendIntegrationPayload($this->integration, $submission);
		}
	}
}
