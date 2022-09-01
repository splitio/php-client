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
     * @return SplitIO\Component\Log\Logger
     */
    private static function setDefaultLogger(array $options)
    {
        $adapter = (isset($options['adapter'])) ? $options['adapter'] : null;
        $level = (isset($options['level'])) ? $options['level'] : null;

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

        if (! LogLevelEnum::isValid($level)) {
            $level = LogLevel::WARNING;
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
        if (!isset($options['psr3-instance'])) {
            return self::setDefaultLogger($options);
        }

        return new Logger(new LoggerAdapterPSR($options['psr3-instance']));
    }
}
