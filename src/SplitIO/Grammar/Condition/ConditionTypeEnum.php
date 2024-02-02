<?php

namespace SplitIO\Grammar\Condition;

use SplitIO\Component\Common\Enum;

class ConditionTypeEnum extends Enum
{
    public const WHITELIST = 'WHITELIST';
    public const ROLLOUT = 'ROLLOUT';
}
