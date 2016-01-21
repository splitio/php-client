<?php
namespace SplitIO\Log\Handler;

use SplitIO\Sdk;
use Psr\Log\LogLevel;

/**
 * Class Stdout
 * Logger Handler to write on system standard output
 * @package SplitIO\Log\Handler
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