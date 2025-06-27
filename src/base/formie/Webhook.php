<?php

namespace onstuimig\formieintegrationtriggers\base\formie;

use onstuimig\formieintegrationtriggers\interfaces\EventTriggersInterface;
use onstuimig\formieintegrationtriggers\traits\EventTriggers;
use verbb\formie\base\Webhook as FormieWebhook;

abstract class Webhook extends FormieWebhook implements EventTriggersInterface
{
	use EventTriggers;
}
