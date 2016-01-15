<?php
namespace SplitIO\Log;

use Psr\Log\LoggerInterface;
use \SplitIO\Log\Handler\LogHandlerInterface;
use SplitIO\Log\Handler\Syslog;

/**
 * Class Logger
 * Implement PSR-3 interface
 * @package SplitIO\Log
 */
class Logger implements LoggerInterface
{
    /**
     * @var null|LogHandlerInterface
     */
    protected $handler=null;

    /** Use PSR-3 Trait */
    use \Psr\Log\LoggerTrait;

    /**
     * Logger constructor
     * @param LogHandlerInterface|null $handler
     */
    public function __construct(LogHandlerInterface $handler = null)
    {
        if ($handler !== null) {

            $this->handler = $handler;

        } else {

            $this->handler = new Syslog();

        }
    }

    /**
     * Log method
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $this->handler->write($level, $message);
    }
}