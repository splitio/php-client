<?php
namespace SplitIO\Component\Log;

use SplitIO\Component\Common\Enum;

class LogLevelEnum extends Enum
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
}
