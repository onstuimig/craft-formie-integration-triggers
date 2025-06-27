<?php

namespace onstuimig\formieintegrationtriggers\base\formie;

use onstuimig\formieintegrationtriggers\interfaces\EventTriggersInterface;
use onstuimig\formieintegrationtriggers\traits\EventTriggers;
use verbb\formie\base\EmailMarketing as FormieEmailMarketing;

abstract class EmailMarketing extends FormieEmailMarketing implements EventTriggersInterface
{
	use EventTriggers;
}
