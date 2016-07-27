<?php
namespace SplitIO;

use SplitIO\Component\Http\Uri;
use SplitIO\Component\Initialization\CacheTrait;
use SplitIO\Component\Initialization\LoggerTrait;
use SplitIO\Exception\Exception;
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

        return new Client($options);
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
