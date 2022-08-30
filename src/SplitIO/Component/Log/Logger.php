<?php
namespace SplitIO\Component\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use \SplitIO\Component\Log\Handler\LogHandlerInterface;
use SplitIO\Component\Log\Handler\VoidHandler;

/**
 * Class Logger
 * Implement PSR-3 interface
 * @package SplitIO\Component\Log
 */
class Logger extends LoggerTrait implements LoggerInterface
{
    /**
     * @var null|LogHandlerInterface
     */
    protected $handler=null;

    /**
     * @var null
     */
    protected $logLevel = null;

    protected $logLevels = array(
        LogLevel::DEBUG     => 7,
        LogLevel::INFO      => 6,
        LogLevel::NOTICE    => 5,
        LogLevel::WARNING   => 4,
        LogLevel::ERROR     => 3,
        LogLevel::CRITICAL  => 2,
        LogLevel::ALERT     => 1,
        LogLevel::EMERGENCY => 0,
    );

    /**
     * Logger constructor
     * @param LogHandlerInterface|null $handler
     */
    public function __construct(LogHandlerInterface $handler = null, $level = LogLevel::WARNING)
    {
        $this->logLevel = $this->logLevels[$level];

        if ($handler !== null) {
            $this->handler = $handler;
        } else {
            $this->handler = new VoidHandler();
        }
    }

    /**
     * Log method
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array()): void
    {
        if ($this->logLevels[$level] <= $this->logLevel) {
            $this->handler->write($level, $message);
        }
    }
}
