<?php
namespace SplitIO\Sdk;

use Psr\Log\LogLevel;
use SplitIO\Log\LogLevelEnum;

class SdkConfig
{
    //LOG
    /** @var string */
    private $logAdapter = 'stdout';

    /** @var string */
    private $logLevel = LogLevel::INFO;

    /** @var null|string */
    private $logCustom = null;

    //CACHE
    /** @var int */
    private $cacheItemTtl = 3600;

    /** @var string */
    private $cacheAdapter = 'filesystem';

    /** @var string */
    private $cacheFilesystemPath = '/tmp';

    /** @var string */
    private $cacheRedisHost = 'localhost';

    /** @var int */
    private $cacheRedisPort = 6379;

    /** @var array */
    private $cacheMemcachedServers = [['localhost',11211]];

    /** @var null|string */
    private $cacheCustom = null;

    /** @var string */
    private $apiKey = 'localhost';

    /**
     * By Default try to load args set by user.
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        if (isset($args['apiKey'])) {
            $this->setApiKey($args['apiKey']);
        }

        //LOG
        if (isset($args['log']['adapter'])) {
            $this->setLogAdapter($args['log']['adapter']);
        }

        if (isset($args['log']['level'])) {
            $this->setLogLevel($args['log']['level']);
        }

        //CACHE
        if (isset($args['cache']['options']['ttl'])) {
            $this->setCacheItemTtl((int) $args['cache']['options']['ttl']);
        }

        if (isset($args['cache']['adapter'])) {
            $this->setCacheAdapter($args['cache']['adapter']);
        }

        if (isset($args['cache']['options']['path'])) {
            $this->setCacheFilesystemPath($args['cache']['options']['path']);
        }

        if (isset($args['cache']['options']['host'])) {
            $this->setCacheRedisHost($args['cache']['options']['host']);
        }

        if (isset($args['cache']['options']['port'])) {
            $this->setCacheRedisPort($args['cache']['options']['port']);
        }

        if (isset($args['cache']['options']['servers'])) {
            $this->setCacheMemcachedServers($args['cache']['options']['servers']);
        }

    }

    /**
     * @param array $args
     * @return SdkConfig
     */
    public static function loadFromCliService(array $args)
    {
        require_once __DIR__."/../../../bin/constants.php";
        $instance = new self;

        if (isset($args[SPLITIO_CONFIG_API_KEY])) {
            $instance->setApiKey($args[SPLITIO_CONFIG_API_KEY]);
        }

        //LOG
        if (isset($args[SPLITIO_CONFIG_LOG_ADAPTER])) {
            $instance->setLogAdapter($args[SPLITIO_CONFIG_LOG_ADAPTER]);
        }

        if (isset($args[SPLITIO_CONFIG_LOG_LEVEL])) {
            $instance->setLogLevel($args[SPLITIO_CONFIG_LOG_LEVEL]);
        }

        if (isset($args[SPLITIO_CONFIG_LOG_CUSTOM])) {
            $instance->setLogCustom($args[SPLITIO_CONFIG_LOG_CUSTOM]);
        }

        //CACHE
        if (isset($args[SPLITIO_CONFIG_CACHE_TTL])) {
            $instance->setCacheItemTtl((int) $args[SPLITIO_CONFIG_CACHE_TTL]);
        }

        if (isset($args[SPLITIO_CONFIG_CACHE_ADAPTER])) {
            $instance->setCacheAdapter($args[SPLITIO_CONFIG_CACHE_ADAPTER]);
        }

        if (isset($args[SPLITIO_CONFIG_CACHE_FILESYSTEM_PATH])) {
            $instance->setCacheFilesystemPath($args[SPLITIO_CONFIG_CACHE_FILESYSTEM_PATH]);
        }

        if (isset($args[SPLITIO_CONFIG_CACHE_REDIS_HOST])) {
            $instance->setCacheRedisHost($args[SPLITIO_CONFIG_CACHE_REDIS_HOST]);
        }

        if (isset($args[SPLITIO_CONFIG_CACHE_REDIS_PORT])) {
            $instance->setCacheRedisPort($args[SPLITIO_CONFIG_CACHE_REDIS_PORT]);
        }

        if (isset($args[SPLITIO_CONFIG_CACHE_MEMCACHED_SERVERS])) {
            $instance->setCacheMemcachedServers($args[SPLITIO_CONFIG_CACHE_MEMCACHED_SERVERS]);
        }

        if (isset($args[SPLITIO_CONFIG_CACHE_CUSTOM])) {
            $instance->setCacheCustom($args[SPLITIO_CONFIG_CACHE_CUSTOM]);
        }

        return $instance;
    }

    /**
     * @return int
     */
    public function getCacheItemTtl()
    {
        return (int) $this->cacheItemTtl;
    }

    /**
     * @param int $cacheItemTtl
     */
    public function setCacheItemTtl($cacheItemTtl)
    {
        $this->cacheItemTtl = $cacheItemTtl;
    }

    /**
     * @return string
     */
    public function getLogAdapter()
    {
        return $this->logAdapter;
    }

    /**
     * @param string $logAdapter
     */
    public function setLogAdapter($logAdapter)
    {
        if (in_array($logAdapter, ['syslog', 'stdout'])) {
            $this->logAdapter = $logAdapter;
        }
    }

    /**
     * @return string
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param string $logLevel
     */
    public function setLogLevel($logLevel)
    {
        if (LogLevelEnum::isValid($logLevel)) {
            $this->logLevel = $logLevel;
        }

    }

    /**
     * @return null|string
     */
    public function getLogCustom()
    {
        return $this->logCustom;
    }

    /**
     * @param null|string $logCustom
     */
    public function setLogCustom($logCustom)
    {
        $this->logCustom = $logCustom;
    }

    /**
     * @return string
     */
    public function getCacheAdapter()
    {
        return $this->cacheAdapter;
    }

    /**
     * @param string $cacheAdapter
     */
    public function setCacheAdapter($cacheAdapter)
    {
        if (in_array($cacheAdapter, ['filesystem', 'redis', 'memcached'])) {
            $this->cacheAdapter = $cacheAdapter;
        }
    }

    /**
     * @return string
     */
    public function getCacheFilesystemPath()
    {
        return $this->cacheFilesystemPath;
    }

    /**
     * @param string $cacheFilesystemPath
     */
    public function setCacheFilesystemPath($cacheFilesystemPath)
    {
        $this->cacheFilesystemPath = $cacheFilesystemPath;
    }

    /**
     * @return string
     */
    public function getCacheRedisHost()
    {
        return $this->cacheRedisHost;
    }

    /**
     * @param string $cacheRedisHost
     */
    public function setCacheRedisHost($cacheRedisHost)
    {
        $this->cacheRedisHost = $cacheRedisHost;
    }

    /**
     * @return int
     */
    public function getCacheRedisPort()
    {
        return $this->cacheRedisPort;
    }

    /**
     * @param int $cacheRedisPort
     */
    public function setCacheRedisPort($cacheRedisPort)
    {
        $this->cacheRedisPort = $cacheRedisPort;
    }

    /**
     * @return array
     */
    public function getCacheMemcachedServers()
    {
        return $this->cacheMemcachedServers;
    }

    /**
     * @param array $cacheMemcachedServers
     */
    public function setCacheMemcachedServers($cacheMemcachedServers)
    {
        if (is_array($cacheMemcachedServers)
            && isset($cacheMemcachedServers[0][0])
            && isset($cacheMemcachedServers[0][1])) { //At least one server configuration

            $this->cacheMemcachedServers = $cacheMemcachedServers;
        }

    }

    /**
     * @return null|string
     */
    public function getCacheCustom()
    {
        return $this->cacheCustom;
    }

    /**
     * @param null|string $cacheCustom
     */
    public function setCacheCustom($cacheCustom)
    {
        $this->cacheCustom = $cacheCustom;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }



}