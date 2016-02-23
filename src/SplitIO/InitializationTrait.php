<?php
/**
 * Created by PhpStorm.
 * User: sarrubia
 * Date: 19/02/16
 * Time: 21:10
 */

namespace SplitIO;


use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use SplitIO\Cache\Pool;
use SplitIO\Log\Handler\Stdout;
use SplitIO\Log\Handler\Syslog;
use SplitIO\Log\Logger;
use SplitIO\Sdk\SdkConfig;

trait InitializationTrait
{
    protected static function initSdk(array $args)
    {
        $apiKey = (isset($args['apiKey'])) ? $args['apiKey'] : 'localhost';

        //Add SDK Configuration values.
        Split::setSplitSdkConfiguration(new SdkConfig($args));

        //Add PSR3 logger instance
        self::addLogger($args);

        //Add PSR6 CachePool instance
        self::addCachePool($args);

        //Add Split Client to hit Split servers.
        self::addSplitClient($apiKey);
    }

    private static function addSplitClient($apiKey)
    {
        Split::setSplitClient(new Client(getSplitServerUrl(), $apiKey));
    }

    private static function addCachePool(array $options)
    {
        $cachePool = null;

        if (isset($options['cache']['psr6-instance']) &&
            $options['cache']['psr6-instance'] instanceof CacheItemPoolInterface) {
            $cachePool = $options['cache']['psr6-instance'];
        } else {
            $cachePoolAdapter = null;
            $sdkConfig = Split::getSplitSdkConfiguration();

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


        Split::setCache($cachePool);
    }

    private static function addLogger(array $options)
    {
        $logger = null;

        if (isset($options['log']['psr3-instance']) &&
            $options['log']['psr3-instance'] instanceof LoggerInterface) {
            $logger = $options['log']['psr3-instance'];
        } else {
            $sdkConfig = Split::getSplitSdkConfiguration();
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

        Split::setLogger($logger);
    }
}