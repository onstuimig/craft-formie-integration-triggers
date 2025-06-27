<?php

namespace onstuimig\formieintegrationtriggers\events;

use yii\base\Event;

class RegisterTriggersEvent extends Event
{
	public ?array $triggers = [];
}
