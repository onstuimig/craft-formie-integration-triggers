<?php

namespace onstuimig\formieintegrationtriggers\base\formie;

use onstuimig\formieintegrationtriggers\interfaces\EventTriggersInterface;
use onstuimig\formieintegrationtriggers\traits\EventTriggers;
use verbb\formie\base\Integration as FormieIntegration;

abstract class Integration extends FormieIntegration implements EventTriggersInterface
{
	use EventTriggers;
}
