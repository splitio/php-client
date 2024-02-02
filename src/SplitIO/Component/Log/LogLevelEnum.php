<?php

namespace SplitIO\Component\Log;

use SplitIO\Component\Common\Enum;

class LogLevelEnum extends Enum
{
    public const EMERGENCY = 'emergency';
    public const ALERT = 'alert';
    public const CRITICAL = 'critical';
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const NOTICE = 'notice';
    public const INFO = 'info';
    public const DEBUG = 'debug';
}
