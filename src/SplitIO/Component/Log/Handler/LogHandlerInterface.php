<?php
namespace SplitIO\Component\Log\Handler;

/**
 * Interface LogHandlerInterface
 * @package SplitIO\Log\Handler
 */
interface LogHandlerInterface
{
    public function write($logLevel, $message);
}
