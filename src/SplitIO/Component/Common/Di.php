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

    private array $factoryTracker = array();

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
     * @param string $apiKey
     * @return int
     */
    public static function trackFactory($apiKey)
    {
        $tracked = 1;
        if (isset(self::getInstance()->factoryTracker[$apiKey])) {
            $currentInstances = self::getInstance()->factoryTracker[$apiKey];
            if ($currentInstances == 1) {
                self::getInstance()->getLogger()->warning(
                    "Factory Instantiation: You already have 1 factory with this API Key. " .
                    "We recommend keeping only one instance of the factory at all times " .
                    "(Singleton pattern) and reusing it throughout your application."
                );
            } else {
                self::getInstance()->getLogger()->warning(
                    "Factory Instantiation: You already have " . $currentInstances . " factories with this API Key. " .
                    "We recommend keeping only one instance of the factory at all times " .
                    "(Singleton pattern) and reusing it throughout your application."
                );
            }
            $tracked = $currentInstances + $tracked;
        } elseif (count(self::getInstance()->factoryTracker) > 0) {
            self::getInstance()->getLogger()->warning(
                "Factory Instantiation: You already have an instance of the Split factory. " .
                "Make sure you definitely want this additional instance. " .
                "We recommend keeping only one instance of the factory at all times " .
                "(Singleton pattern) and reusing it throughout your application."
            );
        }
        self::getInstance()->factoryTracker[$apiKey] = $tracked;
        return $tracked;
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
