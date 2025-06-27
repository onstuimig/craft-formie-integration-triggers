<?php

namespace onstuimig\formieintegrationtriggers\traits;

use Craft;
use onstuimig\formieintegrationtriggers\FormieIntegrationTriggers;
use verbb\formie\elements\Submission;
use verbb\formie\models\IntegrationField;

trait EventTriggers
{
	public ?array $triggerOnEvents = null;

	public function getTriggerOnEvents(): ?array
	{
		return $this->triggerOnEvents;
	}

	// public function getMappedFieldValue(string $mappedFieldValue, Submission $submission, IntegrationField $integrationField): mixed
	public function getMappedFieldValue($mappedFieldValue, $submission, $integrationField): mixed // Formie 2 compatible version
	{
		// If this is a submission attribute, fetch it - easy!
		if (str_starts_with($mappedFieldValue, '{integrationEvent:')) {
			if (!isset($this->context['integrationEvent'])) {
				return null;
			}

			$mappedFieldValue = str_replace(['{integrationEvent:', '}'], ['', ''], $mappedFieldValue);

			if (!isset($this->context['integrationEvent'][$mappedFieldValue])) {
				return null;
			}

			// Ensure the submission value is typecasted properly.
			return static::convertValueForIntegration($this->context['integrationEvent'][$mappedFieldValue], $integrationField);
		}
		
		return parent::getMappedFieldValue($mappedFieldValue, $submission, $integrationField);
	}

	abstract public function getIntegrationFormSettingsHtml($form): string;

	final public function getFormSettingsHtml($form): string
	{
		$triggers = FormieIntegrationTriggers::getInstance()->triggers->getTriggers();

		$triggerOptions = [
			[
				'label' => Craft::t('formie-integration-triggers', 'Select event(s)'),
				'value' => '',
			],
		];
		foreach ($triggers as $triggerClass => $trigger) {
			$triggerOptions[] = [
				'label' => $trigger->displayName(),
				'value' => $triggerClass,
			];
		}

		$formSettingsHtml = Craft::$app->getView()->renderTemplate('formie-integration-triggers/formie/_form-settings', [
			'integration' => $this,
			'form' => $form,
			'triggerOptions' => $triggerOptions,
		]);
		
		return $formSettingsHtml . $this->getIntegrationFormSettingsHtml($form);
	}

	/**
	 * Add Event context options to fields
	 *
	 * @param IntegrationField[] $fields
	 *
	 * @return IntegrationField[]
	 */
	public function addFieldTriggerContextOptions(array &$fields): array
	{
		if (!empty($this->triggerOnEvents)) {
			$triggers = FormieIntegrationTriggers::getInstance()->triggers->getTriggers();

			foreach ($fields as $field) {
				if (!$field->options) {
					$field->options = [
						'label' => Craft::t('formie-integration-triggers', 'Event values'),
						'options' => [],
					];
				} else {
					if ($field->options['label']) {
						$field->options['label'] .= ' + ' . Craft::t('formie-integration-triggers', 'Event values');
					} else {
						$field->options['label'] = Craft::t('formie-integration-triggers', 'Event values');
					}
					$field->options['options'][] = [
						'label' => '-- ' . Craft::t('formie-integration-triggers', 'Event values') . ' --',
						'value' => '',
					];
				}

				foreach ($this->triggerOnEvents as $triggerHandle) {
					$trigger = $triggers[$triggerHandle] ?? null;
					if (!$trigger) {
						continue;
					}

					$eventFields = $trigger->getFields();
					foreach ($eventFields as $eventFieldHandle => $eventFieldName) {
						$field->options['options'][] = [
							'label' => $trigger->displayName() . ' - ' . $eventFieldName,
							'value' => '{integrationEvent:' . $eventFieldHandle . '}',
						];
					}
				}
			}
		}

		return $fields;
	}
}
