<?php
namespace SplitIO\Component\Initialization;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use SplitIO\Component\Common\ServiceProvider;
use SplitIO\Component\Log\Handler\Echos;
use SplitIO\Component\Log\Handler\Stdout;
use SplitIO\Component\Log\Handler\Syslog;
use SplitIO\Component\Log\Handler\VoidHandler;
use SplitIO\Component\Log\Logger;
use SplitIO\Component\Log\LogLevelEnum;

class LoggerTrait
{
    public static function addLogger($adapter, $level, LoggerInterface $custom = null)
    {
        $logger = null;

        if ($custom !== null) {
            $logger = $custom;
        } else {
            $logAdapter = null;

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

            $logger = new Logger($logAdapter, $level);
        }

        ServiceProvider::registerLogger($logger);
    }

    public static function addLoggerFromFile($filePath)
    {
        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }
}
