<?php
namespace SplitIO\Component\Log\Handler;

/**
 * Class Void
 * Logger Handler to prevent write anything
 * @package SplitIO\Component\Log\Handler
 */
class VoidHandler implements LogHandlerInterface
{
    /**
     * Log messages will not be written
     * @param $logLevel
     * @param $message
     */
    public function write($logLevel, $message)
    {
        return;
    }
}
