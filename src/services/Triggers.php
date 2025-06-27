<?php

namespace onstuimig\formieintegrationtriggers\services;

use onstuimig\formieintegrationtriggers\base\EventTrigger;
use onstuimig\formieintegrationtriggers\events\RegisterTriggersEvent;
use yii\base\Component;

/**
 * Triggers service
 */
class Triggers extends Component
{
	public const EVENT_REGISTER_TRIGGERS = 'registerTriggers';

	private array $_triggers = [];
	
	/** @return EventTrigger[] */
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
			$this->_triggers[str_replace('\\', '.', $class)] = new $class();
		}
		
		return $this->_triggers;
	}

	public function getTrigger(string $class): ?EventTrigger
	{
		return $this->getTriggers()[$class] ?? null;
	}
}
