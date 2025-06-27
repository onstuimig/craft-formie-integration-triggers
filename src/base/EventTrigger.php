<?php

namespace onstuimig\formieintegrationtriggers\base;

use craft\helpers\Queue;
use verbb\formie\base\Integration;
use verbb\formie\elements\Submission;
use verbb\formie\Formie;
use verbb\formie\jobs\TriggerIntegration;
use verbb\formie\models\Settings as FormieSettings;

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

		// Set integration context from submission snapshot
		if (isset($submission->snapshot['integrationContext'])) {
			$this->integration->context = $submission->snapshot['integrationContext'];
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
