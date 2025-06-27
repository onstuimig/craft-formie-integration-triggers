<?php

namespace onstuimig\formieintegrationtriggers\base\formie;

use onstuimig\formieintegrationtriggers\interfaces\EventTriggersInterface;
use onstuimig\formieintegrationtriggers\traits\EventTriggers;
use verbb\formie\base\Crm as FormieCrm;

abstract class Crm extends FormieCrm implements EventTriggersInterface
{
	use EventTriggers;
}
