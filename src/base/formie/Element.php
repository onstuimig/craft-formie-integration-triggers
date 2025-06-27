<?php

namespace onstuimig\formieintegrationtriggers\base\formie;

use onstuimig\formieintegrationtriggers\interfaces\EventTriggersInterface;
use onstuimig\formieintegrationtriggers\traits\EventTriggers;
use verbb\formie\base\Element as FormieElement;

abstract class Element extends FormieElement implements EventTriggersInterface
{
	use EventTriggers;
}
