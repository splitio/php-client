<?php
namespace SplitIO\Common;

use Psr\Log\LoggerInterface;
use Psr\Cache\CacheItemPoolInterface;
use SplitIO\Client;
use SplitIO\Client\Config as ClientConfiguration;

/**
 * Class Di
 * @package SplitIO\Common
 */
class Di
{
    const KEY_LOG = 'SPLIT-LOGGER';

    const KEY_CACHE = 'SPLIT-CACHE';

    const KEY_SPLIT_CLIENT = 'SPLIT-CLIENT';

    const KEY_SPLIT_CLIENT_CONFIG = 'SPLIT-CLIENT-CONFIG';

    /**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

    /**
     * @var array
     */
    private $container = [];

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
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

    /**
     * @param $key
     * @param $instance
     */
    public function set($key, $instance)
    {
        $this->container[$key] = $instance;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return (isset($this->container[$key])) ? $this->container[$key] : null;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->container[self::KEY_LOG] = $logger;
    }

    /**
     * @return null|\Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return (isset($this->container[self::KEY_LOG])) ? $this->container[self::KEY_LOG] : null;
    }

    public function setCache(CacheItemPoolInterface $pool)
    {
        $this->container[self::KEY_CACHE] = $pool;
    }

    /**
     * @return null|\Psr\Cache\CacheItemPoolInterface
     */
    public function getCache()
    {
        return (isset($this->container[self::KEY_CACHE])) ? $this->container[self::KEY_CACHE] : null;
    }

    public function setSplitClient(Client $client)
    {
        $this->container[self::KEY_SPLIT_CLIENT] = $client;
    }

    /**
     * @return null|\SplitIO\Client
     */
    public function getSplitClient()
    {
        return (isset($this->container[self::KEY_SPLIT_CLIENT])) ? $this->container[self::KEY_SPLIT_CLIENT] : null;
    }

    public function setSplitClientConfiguration(ClientConfiguration $config)
    {
        $this->container[self::KEY_SPLIT_CLIENT_CONFIG] = $config;
    }

    /**
     * @return null|\SplitIO\Client\Config
     */
    public function getSplitClientConfiguration()
    {
        return (isset($this->container[self::KEY_SPLIT_CLIENT_CONFIG])) ?
                $this->container[self::KEY_SPLIT_CLIENT_CONFIG] : null;
    }

}