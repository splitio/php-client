<?php
namespace SplitIO\Component\Common;

use SplitIO\Component\Log\Logger;

/**
 * Class Di
 * @package SplitIO\Common
 */
class Di
{
    private \SplitIO\Component\Log\Logger|null $logger = null;

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

    private function __trackFactory()
    {
        $this->factoryTracker += 1;
        return $this->factoryTracker;
    }

    /**
     * @param \SplitIO\Component\Log\Logger $logger
     */
    private function __setLogger(Logger $logger)
    {
        if (is_null($this->logger)) {
            $this->logger = $logger;
            return;
        }
        $this->logger->debug("logger was set before, ignoring new instance provided");
    }

    /**
     * @return null|\SplitIO\Component\Log\Logger
     */
    private function __getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string $ip
     */
    private function __setIPAddress(string $ip)
    {
        if (empty($this->ipAddress)) {
            $this->ipAddress = $ip;
            return;
        }
        if (!(is_null($this->logger))) {
            $this->logger->debug("IPAddress was set before, ignoring new instance provided");
        }
    }

    /**
     * @return null|string
     */
    private function __getIPAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return int
     */
    public static function trackFactory()
    {
        return self::getInstance()->__trackFactory();
    }

    /**
     * @param \SplitIO\Component\Log\Logger $logger
     */
    public static function setLogger(Logger $logger)
    {
        self::getInstance()->__setLogger($logger);
    }

    /**
     * @return null|\SplitIO\Component\Log\Logger
     */
    public static function getLogger()
    {
        return self::getInstance()->__getLogger();
    }

    /**
     * @param string $ip
     */
    public static function setIPAddress(string $ip)
    {
        self::getInstance()->__setIPAddress($ip);
    }

    /**
     * @return null|string
     */
    public static function getIPAddress()
    {
        return self::getInstance()->__getIPAddress();
    }
}
