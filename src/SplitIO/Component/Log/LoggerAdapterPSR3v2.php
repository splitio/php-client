<?php
namespace SplitIO\Component\Log;

use Psr\Log\LoggerInterface;
use SplitIO\Component\Log\Handler\LogHandlerInterface;

/**
 * Class LoggerAdapterPSR3v2
 * Logger Handler for PSR3 v2
 * @package namespace SplitIO\Component\Log;
 */
class LoggerAdapterPSR3v2 implements LogHandlerInterface
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
        try {
            if (!is_string($message) || !$message instanceof Stringable) {
                $message = json_encode($message);
            }
            $this->logger->write($logLevel, $message);
        } catch (\Exception $e) {
        }
    }
}
