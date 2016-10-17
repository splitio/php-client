<?php
namespace SplitIO\Component\Log\Handler;

/**
 * Class Stdout
 * Logger Handler to write on system standard output
 * @package SplitIO\Component\Log\Handler
 */
class Stdout implements LogHandlerInterface
{
    /**
     * Write on standard output
     * @param $logLevel
     * @param $message
     */
    public function write($logLevel, $message)
    {
        echo sprintf('<%s> %s', $logLevel, $message) . PHP_EOL;
    }
}
