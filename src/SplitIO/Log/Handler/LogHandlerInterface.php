<?php
/**
 * Created by PhpStorm.
 * User: sarrubia
 * Date: 14/01/16
 * Time: 21:44
 */

namespace SplitIO\Log\Handler;

/**
 * Interface LogHandlerInterface
 * @package SplitIO\Log\Handler
 */
interface LogHandlerInterface
{
    public function write($logLevel, $message);
}