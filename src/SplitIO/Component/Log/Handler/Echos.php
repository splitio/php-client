<?php

namespace SplitIO\Component\Log\Handler;

/**
 * Class Echos
 * Logger Handler to write on system standard output
 * @package SplitIO\Component\Log\Handler
 */
class Echos implements LogHandlerInterface
{
    /**
     * Write on output
     * @param $logLevel
     * @param $message
     */
    public function write($logLevel, $message)
    {
        echo sprintf('<%s> %s', $logLevel, $message) . PHP_EOL;
    }
}
