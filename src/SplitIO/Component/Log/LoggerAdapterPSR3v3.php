<?php
namespace SplitIO\Component\Log;

use Psr\Log\LoggerInterface;
use SplitIO\Component\Log\Handler\LogHandlerInterface;

/**
 * Class LoggerAdapterPSR3v3
 * Logger Handler for PSR3v3
 * @package namespace SplitIO\Component\Log;
 */
class LoggerAdapterPSR3v3 implements LogHandlerInterface
{
    /**
     * @var LogHandlerInterface
     */
    protected $logger;

    /**
     * Logger constructor
     * @param LoggerAdapterPSR2 $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log messages for PSR3v3
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
