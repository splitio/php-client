<?php
namespace SplitIO\Component\Initialization;

use Psr\Log\LogLevel;
use SplitIO\Component\Log\Logger;
use SplitIO\Component\Log\LoggerAdapterPSR;
use SplitIO\Component\Log\LogLevelEnum;
use SplitIO\Component\Log\Handler\Echos;
use SplitIO\Component\Log\Handler\Stdout;
use SplitIO\Component\Log\Handler\Syslog;
use SplitIO\Component\Log\Handler\VoidHandler;

class LoggerFactory
{
    /**
     * Builds defaultLogger
     *
     * @param $options
     * @param $level
     * @return SplitIO\Component\Log\Logger
     */
    private static function setDefaultLogger(array $options, $level)
    {
        $adapter = (isset($options['adapter'])) ? $options['adapter'] : null;

        switch ($adapter) {
            case 'stdout':
                $logAdapter = new Stdout();
                break;

            case 'echo':
                $logAdapter = new Echos();
                break;

            case 'void':
                $logAdapter = new VoidHandler();
                break;

            case 'syslog':
            default:
                $logAdapter = new Syslog();
                break;
        }



        return new Logger($logAdapter, $level);
    }

    /**
     * Builds logger
     *
     * @param $options
     * @return SplitIO\Component\Log\Logger
     */
    public static function setupLogger(array $options)
    {
        $level = (isset($options['level'])) ? $options['level'] : null;
        if (!LogLevelEnum::isValid($level)) {
            $level = LogLevel::WARNING;
        }

        if (!isset($options['psr3-instance'])) {
            return self::setDefaultLogger($options, $level);
        }

        return new Logger(new LoggerAdapterPSR($options['psr3-instance']), $level);
    }
}
