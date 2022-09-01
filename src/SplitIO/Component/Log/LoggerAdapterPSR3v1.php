<?php
namespace SplitIO\Component\Log;

use Psr\Log\LoggerInterface;
use SplitIO\Component\Log\Handler\LogHandlerInterface;

/**
 * Class LoggerAdapterPSR3v1
 * Logger Handler for PSR3 v1
 * @package namespace SplitIO\Component\Log;
 */
class LoggerAdapterPSR3v1 implements LogHandlerInterface
{
    /**
     * @var LogHandlerInterface
     */
    protected $logger;

    /**
     * Logger constructor
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log messages for PSR3
     * @param $logLevel
     * @param $message
     */
    public function write($logLevel, $message)
    {
        $this->logger->log($logLevel, $message);
    }
}
