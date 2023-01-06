<?php
namespace SplitIO\Component\Common;

use SplitIO\Component\Log\Logger;

/**
 * Class Di
 * @package SplitIO\Common
 */
class Di
{
    private \SplitIO\Component\Log\Logger $logger;

    private int $factoryTracker = 0;

    private string $ipAddress = "";

    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return self The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    public function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    public function __wakeup()
    {
    }

    /**
     * @return int
     */
    public static function trackFactory()
    {
        self::getInstance()->factoryTracker += 1;
        return self::getInstance()->factoryTracker;
    }

    /**
     * @param \SplitIO\Component\Log\Logger $logger
     */
    public static function setLogger(Logger $logger)
    {
        if (!isset(self::getInstance()->logger)) {
            self::getInstance()->logger = $logger;
            return;
        }
        self::getInstance()->logger->debug("logger was set before, ignoring new instance provided");
    }

    /**
     * @return \SplitIO\Component\Log\Logger
     */
    public static function getLogger()
    {
        if (!isset(self::getInstance()->logger)) {
            throw new Exception("logger was not set yet");
        }
        return self::getInstance()->logger;
    }

    /**
     * @param string $ip
     */
    public static function setIPAddress(string $ip)
    {
        if (empty(self::getInstance()->ipAddress)) {
            self::getInstance()->ipAddress = $ip;
            return;
        }
        self::getInstance()->getLogger()->debug("IPAddress was set before, ignoring new instance provided");
    }

    /**
     * @return string
     */
    public static function getIPAddress()
    {
        return self::getInstance()->ipAddress;
    }
}
