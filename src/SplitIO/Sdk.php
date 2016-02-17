<?php
namespace SplitIO;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use SplitIO\Log\Handler\Stdout;
use SplitIO\Log\Handler\Syslog;
use SplitIO\Log\Logger;
use SplitIO\Log\LogLevelEnum;
use SplitIO\Common\Di;
use SplitIO\Cache\Pool;
use SplitIO\Sdk\Client as SdkClient;
use SplitIO\Client;
use SplitIO\Sdk\SdkConfig;

class Sdk
{
    const VERSION = '0.0.1';

    const NAME = 'Split-SDK-PHP';

    const SPLITIO_URL = "https://sdk.split.io";

    /** @var array Arguments for creating clients */
    private $args;

    private function __construct()
    {
    }

    /**
     * @param $apiKey
     * @param array $args
     * @return \SplitIO\Sdk\Client
     */
    public static function factory($apiKey, array $args = [])
    {
        //Adding API Key into args array.
        $args['apiKey'] = $apiKey;

        //Add SDK Configuration values.
        Di::getInstance()->setSplitSdkConfiguration(new SdkConfig($args));

        //Add PSR3 logger instance
        self::addLogger($args);

        //Add PSR6 CachePool instance
        self::addCachePool($args);

        //Add Split Client to hit Split servers.
        self::addSplitClient($apiKey);

        return new SdkClient($apiKey, $args);
    }

    private static function addSplitClient($apiKey)
    {
        DI::getInstance()->setSplitClient(new Client(getSplitServerUrl(), $apiKey));
    }

    private static function addCachePool(array $options)
    {
        $cachePool = null;

        if (isset($options['cache']['psr6-instance']) &&
            $options['cache']['psr6-instance'] instanceof CacheItemPoolInterface) {
            $cachePool = $options['cache']['psr6-instance'];
        } else {
            $cachePoolAdapter = null;
            $sdkConfig = Di::getInstance()->getSplitSdkConfiguration();

            switch ($sdkConfig->getCacheAdapter()) {
                case 'memcached':
                    $cachePoolAdapter = [
                        'name' => 'memcached',
                        'options' => [
                            'servers' => $sdkConfig->getCacheMemcachedServers()
                        ]
                    ];
                    break;
                case 'redis':
                    $cachePoolAdapter = [
                        'name' => 'redis',
                        'options' => [
                            'host' => $sdkConfig->getCacheRedisHost(),
                            'port' => $sdkConfig->getCacheRedisPort(),
                            'password' => $sdkConfig->getCacheRedisPassword()
                        ]
                    ];
                    break;
                case 'filesystem':
                default:
                    $cachePoolAdapter = [
                        'name' => 'filesystem',
                        'options' => [
                            'path'=> $sdkConfig->getCacheFilesystemPath()
                        ]
                    ];
                    break;
            }

            $cachePool = new Pool([ 'adapter' => $cachePoolAdapter ]);
        }


        Di::getInstance()->setCache($cachePool);
    }

    private static function addLogger(array $options)
    {
        $logger = null;

        if (isset($options['log']['psr3-instance']) &&
            $options['log']['psr3-instance'] instanceof LoggerInterface) {
            $logger = $options['log']['psr3-instance'];
        } else {
            $sdkConfig = Di::getInstance()->getSplitSdkConfiguration();
            $logAdapter = null;

            switch ($sdkConfig->getLogAdapter()) {
                case 'syslog':
                    $logAdapter = new Syslog();
                    break;

                case 'stdout':
                default:
                    $logAdapter = new Stdout();
                    break;
            }

            $logger = new Logger($logAdapter, $sdkConfig->getLogLevel());
        }

        Di::getInstance()->setLogger($logger);
    }
}
