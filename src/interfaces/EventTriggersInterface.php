<?php

namespace onstuimig\formieintegrationtriggers\interfaces;

use verbb\formie\models\IntegrationField;

interface EventTriggersInterface
{
	public function getTriggerOnEvents(): ?array;
	
	/**
	 * Add Event context options to fields
	 *
	 * @param IntegrationField[] $fields
	 *
	 * @return IntegrationField[]
	 */
	public function addFieldTriggerContextOptions(array &$fields): array;

	public function getIntegrationFormSettingsHtml($form): string;
}
