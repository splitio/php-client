<?php
namespace SplitIO;

use SplitIO\Component\Initialization\CacheTrait;
use SplitIO\Component\Initialization\LoggerTrait;
use SplitIO\Exception\Exception;
use SplitIO\Sdk\Factory\LocalhostSplitFactory;
use SplitIO\Sdk\Factory\SplitFactory;
use SplitIO\Component\Common\Di;
use SplitIO\Engine\Splitter;
use SplitIO\Sdk\Factory\SplitFactoryInterface;

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
     * @return \SplitIO\Sdk\Factory\SplitFactoryInterface|null
     */
    public static function factory($apiKey = 'localhost', array $options = array())
    {
        //Adding API Key into args array.
        $options['apiKey'] = $apiKey;

        if (self::instanceExists()) {
            Di::getLogger()->critical("Factory Instantiation: creating multiple factories is not possible. "
                                      . "You have already created a factory.");
            return null;
        }

        $factory = self::makeFactory($apiKey, $options);
        self::registerInstance($factory);
        return $factory;
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
            $_options['predis-sentinels'] = isset($options['sentinels']) ? $options['sentinels'] : null;
            $_options['predis-clusterNodes'] = isset($options['clusterNodes']) ? $options['clusterNodes'] : null;
            $_options['predis-distributedStrategy'] = isset($options['distributedStrategy'])
                ? $options['distributedStrategy'] : null;
        } else {
            throw new Exception("A valid cache system is required. Given: $cacheAdapter");
        }

        CacheTrait::addCache($cacheAdapter, $_options);
    }

    private static function setIP($ip)
    {
        \SplitIO\Component\Common\Di::set('ipAddress', $ip);
    }

    /**
     * Check factory instance
     */
    private static function instanceExists()
    {
        $factory = self::getInstance();
        if (!$factory instanceof SplitFactoryInterface) {
            return false;
        }
        return true;
    }

    /**
     * @return SplitFactoryInterface|mixed
     */
    private static function getInstance()
    {
        return Di::get(Di::KEY_FACTORY);
    }

    /**
     * Register factory instance
     * @param SplitFactoryInterface $factory
     */
    private static function registerInstance($factory)
    {
        Di::set(Di::KEY_FACTORY, $factory);
    }

    /**
     * @param $apiKey
     * @param array $options
     * @return LocalhostSplitFactory|SplitFactory
     */
    private static function makeFactory($apiKey, array $options)
    {
        if ($apiKey == 'localhost') {
            //Register Logger
            self::registerLogger((isset($options['log'])) ? $options['log'] : array());

            return new LocalhostSplitFactory($options);
        } else {
            //Register Logger
            self::registerLogger((isset($options['log'])) ? $options['log'] : array());

            //Register Cache
            self::registerCache((isset($options['cache'])) ? $options['cache'] : array());

            if (isset($options['ipAddress'])) {
                self::setIP($options['ipAddress']);
            }

            Di::set('splitter', new Splitter());

            return new SplitFactory($apiKey, $options);
        }
    }

    /**
     * @param $apiKey
     * @param array $sdkConfig
     * @return SplitFactoryInterface
     */
    public static function singleton($apiKey, array $sdkConfig)
    {
        if (self::instanceExists()) {
            return self::getInstance();
        }

        $factory = self::makeFactory($apiKey, $sdkConfig);
        self::registerInstance($factory);
        return $factory;
    }
}
