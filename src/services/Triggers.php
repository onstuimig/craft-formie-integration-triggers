<?php

namespace onstuimig\formieintegrationtriggers\services;

use onstuimig\formieintegrationtriggers\base\EventTrigger;
use onstuimig\formieintegrationtriggers\events\RegisterTriggersEvent;
use verbb\formie\base\Integration;
use yii\base\Component;

/**
 * Triggers service
 */
class Triggers extends Component
{
	public const EVENT_REGISTER_TRIGGERS = 'registerTriggers';

	private array $_triggers = [];

	private array $_triggerObjects = [];
	
	/** @return string[] */
	public function getTriggers(): array
	{
		if (count($this->_triggers)) {
			return $this->_triggers;
		}

		$triggers = [];

		$event = new RegisterTriggersEvent([
			'triggers' => $triggers,
		]);

		$this->trigger(self::EVENT_REGISTER_TRIGGERS, $event);

		$event->triggers = array_unique($event->triggers);

		foreach ($event->triggers as $class) {
			$this->_triggers[str_replace('\\', '.', $class)] = $class;
		}
		
		return $this->_triggers;
	}

	public function getTrigger(string $handle, ?Integration $integration = null): ?EventTrigger
	{
		$integrationHandle = '_global_';

		if($integration) {
			$integrationHandle = $integration->handle;
		}

		if(isset($this->_triggerObjects[$handle][$integrationHandle])) {
			return $this->_triggerObjects[$handle][$integrationHandle];
		}

		$class = str_replace('.', '\\', $handle);

		if(!in_array($class, $this->getTriggers())) {
			return null;
		}

		$trigger = null;

		try {
			/** @var EventTrigger */
			$trigger = new $class();

			if($integration) {
				$trigger->init($integration);
			}
		} catch (\Throwable $th) { }

		$this->_triggerObjects[$handle][$integrationHandle] = $trigger;

		return $trigger;
	}
}
