<?php
namespace SplitIO;

use SplitIO\Component\Cache\BlockUntilReadyCache;
use SplitIO\Component\Http\Uri;
use SplitIO\Component\Initialization\CacheTrait;
use SplitIO\Component\Initialization\LoggerTrait;
use SplitIO\Component\Stats\Latency;
use SplitIO\Exception\Exception;
use SplitIO\Exception\TimeOutException;
use SplitIO\Sdk\Client;
use SplitIO\Sdk\LocalhostClient;

class Sdk
{
    /**
     * Sdk class should be used as statically
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * @param $apiKey
     * @param array $options
     * @return \SplitIO\Sdk\ClientInterface
     */
    public static function factory($apiKey = 'localhost', array $options = array())
    {
        if ($apiKey == 'localhost') {
            $filePath = (isset($options['splitFile']) && file_exists($options['splitFile']))
                        ? $options['splitFile']
                        : null;
            return new LocalhostClient($filePath);
        }

        //Adding API Key into args array.
        $options['apiKey'] = $apiKey;

        //Register Logger
        self::registerLogger((isset($options['log'])) ? $options['log'] : array());

        //Register Cache
        self::registerCache((isset($options['cache'])) ? $options['cache'] : array());

        //Block Until Ready
        if (isset($options['ready']) && $options['ready'] > 0) {
            if (!self::blockUntilReady($options['ready'])) {
                throw new TimeOutException("Cache data is not ready yet");
            }
        }

        return new Client($options);
    }

    /**
     * @param $timeout
     * @return bool
     */
    private static function blockUntilReady($timeout)
    {
        $bur = new BlockUntilReadyCache();

        $startTime = Latency::startMeasuringLatency();

        do {

            $lastreadyCheckpoint = $bur->getReadyCheckpoint();

            if ($lastreadyCheckpoint > 0) {
                return true;
            }

            // Checkpoint in milliseconds
            $checkPoint = Latency::calculateLatency($startTime) / 1000;

            // waiting 10 milliseconds
            usleep(10000);

        } while ($checkPoint < $timeout);

        return false;
    }

    /**
     * Register the logger class
     */
    private static function registerLogger(array $options)
    {
        if (isset($options['psr3-instance'])) {
            LoggerTrait::addLogger(null, null, $options['psr3-instance']);
        } else {
            $adapter = (isset($options['adapter'])) ? $options['adapter'] : null;
            $level = (isset($options['level'])) ? $options['level'] : null;

            LoggerTrait::addLogger($adapter, $level);
        }
    }

    private static function registerCache(array $options)
    {
        $_options = array();
        $cacheAdapter = isset($options['adapter']) ? $options['adapter'] : 'redis';

        if ($cacheAdapter == 'redis') {
            if (isset($options['options']['url']) && !empty($options['options']['url'])) {
                $uri = new Uri($options['options']['url']);

                $_options['redis-host'] = $uri->getHost();
                $_options['redis-port'] = $uri->getPort();
                $_options['redis-pass'] = $uri->getPass();
            } else {
                $_options['redis-host'] = isset($options['options']['host']) ? $options['options']['host'] : null;
                $_options['redis-port'] = isset($options['options']['port']) ? $options['options']['port'] : null;
                $_options['redis-pass'] = isset($options['options']['pass']) ? $options['options']['pass'] : null;
            }

            $_options['redis-timeout'] = isset($options['options']['timeout']) ? $options['options']['timeout'] : null;
        } elseif ($cacheAdapter == 'predis') {
            $_options['predis-options'] = isset($options['options']) ? $options['options'] : null;
            $_options['predis-parameters'] = isset($options['parameters']) ? $options['parameters'] : null;
        } else {
            throw new Exception("A valid cache system is required. Given: $cacheAdapter");
        }

        CacheTrait::addCache($cacheAdapter, $_options);
    }
}
