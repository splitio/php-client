<?php

namespace SplitIO\Component\Common;

use SplitIO\Component\Log\Logger;
use SplitIO\Exception\Exception;

/**
 * Class Context
 * @package SplitIO\Common
 */
class Context
{
    private const SAME_SDK_KEY = "Factory Instantiation: You already have %s factory/factories with this SDK Key. "
        . "We recommend keeping only one instance of the factory at all times (Singleton pattern) and "
        . "reusing it throughout your application.";

    private const MULTIPLE_INSTANCES = "Factory Instantiation: You already have an instance of the Split factory. "
        . "Make sure you definitely want this additional instance. We recommend keeping only one instance of "
        . "the factory at all times (Singleton pattern) and reusing it throughout your application.";

    private Logger $logger;

    private array $factoryTracker = array();

    private string $ipAddress = "";

    /**
     * @var Context The reference to *Singleton* instance of this class
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
     * @param string $sdkKey
     * @return int
     */
    public static function trackFactory($sdkKey)
    {
        $current = self::getInstance()->factoryTracker[$sdkKey] ?? 0;
        if ($current > 0) {
            self::getInstance()->getLogger()->warning(sprintf(self::SAME_SDK_KEY, $current));
        } elseif (count(self::getInstance()->factoryTracker) > 0) {
            self::getInstance()->getLogger()->warning(self::MULTIPLE_INSTANCES);
        }
        $current += 1;
        self::getInstance()->factoryTracker[$sdkKey] = $current;
        return $current;
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
