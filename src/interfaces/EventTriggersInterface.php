<?php
namespace onstuimig\formieintegrationtriggers\interfaces;

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
	public function addFieldEventContextOptions(array &$fields): array;

	public function getIntegrationFormSettingsHtml($form): string;
}
