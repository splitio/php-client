<?php

namespace SplitIO\Component\Log;

use Psr\Log\LoggerInterface;
use SplitIO\Component\Log\Handler\LogHandlerInterface;

/**
 * Class PSR3LoggerAdapter
 * Logger Handler for PSR3
 * @package namespace SplitIO\Component\Log;
 */
class PSR3LoggerAdapter implements LogHandlerInterface
{
    /**
     * @var LogHandlerInterface
     */
    private $logger;

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
        if (!is_string($message) || !$message instanceof Stringable) {
            try {
                $this->logger->log($logLevel, json_encode($message));
            } catch (\Exception $e) {
                $this->logger->log(
                    LogLevel::ERROR,
                    "error serializing non-stringable object when trying to log message of type " + gettype($message)
                );
            }
        } else {
            $this->logger->log($logLevel, $message);
        }
    }
}
