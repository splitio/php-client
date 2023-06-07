<?php
namespace SplitIO;

use SplitIO\Component\Initialization\LoggerFactory;
use SplitIO\Exception\Exception;
use SplitIO\Sdk\Factory\LocalhostSplitFactory;
use SplitIO\Sdk\Factory\SplitFactory;
use SplitIO\Component\Common\Context;
use SplitIO\Component\Cache\Pool;

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
     * @param $sdkKey
     * @param array $options
     * @return \SplitIO\Sdk\Factory\SplitFactoryInterface
     */
    public static function factory($sdkKey = 'localhost', array $options = array())
    {
        //Adding SDK Key into args array.
        $options['sdkKey'] = $sdkKey;

        //Register Logger
        self::registerLogger((isset($options['log'])) ? $options['log'] : array());

        //Tracking Factory Instantiation
        Context::trackFactory($sdkKey);

        if ($sdkKey == 'localhost') {
            return new LocalhostSplitFactory($options);
        } else {
            //Register Cache
            $cache = self::configureCache((isset($options['cache'])) ? $options['cache'] : array());

            if (isset($options['ipAddress'])) {
                self::setIP($options['ipAddress']);
            }

            return new SplitFactory($sdkKey, $cache, $options);
        }
    }

    /**
     * Register the logger class
     */
    private static function registerLogger(array $options)
    {
        $logger = LoggerFactory::setupLogger($options);
        Context::setLogger($logger);
    }

    private static function configureCache(array $options)
    {
        $_options = array();
        $cacheAdapter = isset($options['adapter']) ? $options['adapter'] : 'redis';

        if ($cacheAdapter == 'redis') {
            throw new Exception("'redis' adapter is not longer supported. Please use 'predis' instead");
        } elseif ($cacheAdapter == 'predis') {
            $_options['options'] = isset($options['options']) ? $options['options'] : null;
            $_options['parameters'] = isset($options['parameters']) ? $options['parameters'] : null;
            $_options['sentinels'] = isset($options['sentinels']) ? $options['sentinels'] : null;
            $_options['clusterNodes'] = isset($options['clusterNodes']) ? $options['clusterNodes'] : null;
            $_options['distributedStrategy'] = isset($options['distributedStrategy'])
                ? $options['distributedStrategy'] : null;
        } else {
            throw new Exception("A valid cache system is required. Given: $cacheAdapter");
        }

        $adapter_config = array(
            'name' => 'predis',
            'options' => $_options,
        );

        return new Pool(array('adapter' => $adapter_config));
    }

    private static function setIP($ip)
    {
        Context::setIPAddress($ip);
    }
}
