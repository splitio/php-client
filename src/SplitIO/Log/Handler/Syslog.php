<?php
namespace SplitIO\Log\Handler;

use SplitIO\Sdk;
use Psr\Log\LogLevel;

/**
 * Class Syslog
 * Logger Handler to write on system log service
 * @package SplitIO\Log\Handler
 */
class Syslog implements LogHandlerInterface
{
    /** @var array Mapping from PSR log levels to syslog levels */
    protected $logLevels = array(
        LogLevel::DEBUG     => LOG_DEBUG,
        LogLevel::INFO      => LOG_INFO,
        LogLevel::NOTICE    => LOG_NOTICE,
        LogLevel::WARNING   => LOG_WARNING,
        LogLevel::ERROR     => LOG_ERR,
        LogLevel::CRITICAL  => LOG_CRIT,
        LogLevel::ALERT     => LOG_ALERT,
        LogLevel::EMERGENCY => LOG_EMERG,
    );

    /**
     * Write on system log service
     * @param $logLevel
     * @param $message
     */
    public function write($logLevel, $message)
    {
        openlog(Sdk::NAME, LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER);

        $_message = sprintf('<%s> %s', $logLevel, $message);

        if (isset($this->logLevels[$logLevel])) {
            syslog($this->logLevels[$logLevel], $_message);
        } else {
            syslog(LOG_INFO, $_message);
        }

        closelog();
    }
}
