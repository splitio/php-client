<?php
namespace SplitIO\Component\Initialization;

use SplitIO\Component\Log\Logger;
use SplitIO\Component\Log\LoggerAdapterPSR3;

class LoggerFactory
{
    private static function setDefaultLogger(array $options) {
        $adapter = (isset($options['adapter'])) ? $options['adapter'] : null;
        $level = (isset($options['level'])) ? $options['level'] : null;

        LoggerTrait::addLogger($adapter, $level);
    }

    public static function setupLogger(array $options) {
        if (!isset($options['psr3-instance'])) {
            self::setDefaultLogger($options);
            return;
        }

        LoggerTrait::addLogger(null, null, new Logger(new LoggerAdapterPSR3($options['psr3-instance'])));
    }
}
