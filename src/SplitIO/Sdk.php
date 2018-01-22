<?php
namespace SplitIO;

use SplitIO\Component\Http\Uri;
use SplitIO\Component\Initialization\CacheTrait;
use SplitIO\Component\Initialization\LoggerTrait;
use SplitIO\Exception\Exception;
use SplitIO\Sdk\Factory\LocalhostSplitFactory;
use SplitIO\Sdk\Factory\SplitFactory;

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
     * @return \SplitIO\Sdk\Factory\SplitFactoryInterface
     */
    public static function factory($apiKey = 'localhost', array $options = array())
    {
        //Adding API Key into args array.
        $options['apiKey'] = $apiKey;

        if ($apiKey == 'localhost') {
            return new LocalhostSplitFactory($options);
        } else {
            //Register Logger
            self::registerLogger((isset($options['log'])) ? $options['log'] : array());

            //Register Cache
            self::registerCache((isset($options['cache'])) ? $options['cache'] : array());

            if (isset($options['ipAddress'])) {
                self::setIP($options['ipAddress']);
            }

            return new SplitFactory($apiKey, $options);
        }
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
            throw new Exception("'redis' adapter is not longer supported. Please use 'predis' instead");
        } elseif ($cacheAdapter == 'predis') {
            $_options['predis-options'] = isset($options['options']) ? $options['options'] : null;
            $_options['predis-parameters'] = isset($options['parameters']) ? $options['parameters'] : null;
        } else {
            throw new Exception("A valid cache system is required. Given: $cacheAdapter");
        }

        CacheTrait::addCache($cacheAdapter, $_options);
    }

    private static function setIP($ip)
    {
        \SplitIO\Component\Common\Di::set('ipAddress', $ip);
    }
}
