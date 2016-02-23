<?php
namespace SplitIO;

use SplitIO\Common\Di;
use Psr\Log\LoggerInterface;
use Psr\Cache\CacheItemPoolInterface;
use SplitIO\Client\Config as ClientConfiguration;
use SplitIO\Sdk\SdkConfig;

class Split
{

    /**
     * @return null|\Psr\Log\LoggerInterface
     */
    public static function logger()
    {
        return Di::getInstance()->getLogger();
    }

    /**
     * @return null|\Psr\Cache\CacheItemPoolInterface|Cache\Pool
     */
    public static function cache()
    {
        return Di::getInstance()->getCache();
    }

    /**
     * @return \SplitIO\Common\Di
     */
    public static function container()
    {
        return Di::getInstance();
    }

    /**
     * @param LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger)
    {
        Di::getInstance()->setLogger($logger);
    }

    /**
     * @param CacheItemPoolInterface $pool
     */
    public static function setCache(CacheItemPoolInterface $pool)
    {
        Di::getInstance()->setCache($pool);
    }

    /**
     * @param Client $client
     */
    public static function setSplitClient(Client $client)
    {
        Di::getInstance()->setSplitClient($client);
    }

    /**
     * @return null|\SplitIO\Client
     */
    public static function getSplitClient()
    {
        return Di::getInstance()->getSplitClient();
    }

    /**
     * @param ClientConfiguration $config
     */
    public static function setSplitClientConfiguration(ClientConfiguration $config)
    {
        Di::getInstance()->setSplitClientConfiguration($config);
    }

    /**
     * @return null|\SplitIO\Client\Config
     */
    public static function getSplitClientConfiguration()
    {
        return Di::getInstance()->getSplitClientConfiguration();
    }

    /**
     * @param SdkConfig $config
     */
    public static function setSplitSdkConfiguration(SdkConfig $config)
    {
        Di::getInstance()->setSplitSdkConfiguration($config);
    }

    /**
     * @return null|\SplitIO\Sdk\SdkConfig
     */
    public static function getSplitSdkConfiguration()
    {
        return Di::getInstance()->getSplitSdkConfiguration();
    }
}
