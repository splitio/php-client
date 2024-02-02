<?php

namespace SplitIO\Component\Initialization;

use Psr\Log\LogLevel;
use SplitIO\Component\Log\Logger;
use SplitIO\Component\Log\PSR3LoggerAdapter;
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
     * @return \SplitIO\Component\Log\Handler\LogHandlerInterface
     */
    private static function buildAdapter(array $options)
    {
        $adapter = (isset($options['adapter'])) ? $options['adapter'] : null;

        switch ($adapter) {
            case 'stdout':
                return new Stdout();

            case 'echo':
                return new Echos();

            case 'void':
                return new VoidHandler();

            case 'syslog':
            default:
                return new Syslog();
        }
    }

    /**
     * Builds logger
     *
     * @param $options
     * @return \SplitIO\Component\Log\Logger
     */
    public static function setupLogger(array $options)
    {
        $level = (isset($options['level'])) ? $options['level'] : null;
        if (!LogLevelEnum::isValid($level)) {
            $level = LogLevel::WARNING;
        }

        if (!isset($options['psr3-instance'])) {
            return new Logger(self::buildAdapter($options), $level);
        }

        return new Logger(new PSR3LoggerAdapter($options['psr3-instance']), $level);
    }
}
