<?php

namespace onstuimig\formieintegrationtriggers\base\formie;

use onstuimig\formieintegrationtriggers\interfaces\EventTriggersInterface;
use onstuimig\formieintegrationtriggers\traits\EventTriggers;
use verbb\formie\base\Miscellaneous as FormieMiscellaneous;

abstract class Miscellaneous extends FormieMiscellaneous implements EventTriggersInterface
{
	use EventTriggers;
}
