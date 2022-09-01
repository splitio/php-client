<?php
namespace SplitIO\Component\Initialization;

use SplitIO\Component\Log\Logger;
use SplitIO\Component\Log\LoggerAdapterPSR3v3;
use SplitIO\Component\Log\LoggerAdapterPSR3v2;
use SplitIO\Component\Log\LoggerAdapterPSR3v1;

class LoggerFactory
{
    public static function setupLogger(array $options) {
        if (!isset($options['psr3-instance'])) {
            $adapter = (isset($options['adapter'])) ? $options['adapter'] : null;
            $level = (isset($options['level'])) ? $options['level'] : null;

            LoggerTrait::addLogger($adapter, $level);
            return;
        }

        $standard = 'psr3-v3';
        if (isset($options['standard'])) {
            $standard = $options['standard'];
        }

        switch ($standard) {
            case 'psr3-v3':
                LoggerTrait::addLogger(null, null, new Logger(new LoggerAdapterPSR3v3($options['psr3-instance'])));
                break;
            case 'psr3-v2':
                LoggerTrait::addLogger(null, null, new Logger(new LoggerAdapterPSR3v2($options['psr3-instance'])));
                break;
            case 'psr3-v1':
                LoggerTrait::addLogger(null, null, new Logger(new LoggerAdapterPSR3v1($options['psr3-instance'])));
                break;
            default:
                $adapter = (isset($options['adapter'])) ? $options['adapter'] : null;
                $level = (isset($options['level'])) ? $options['level'] : null;

                LoggerTrait::addLogger($adapter, $level);
        }
    }
}