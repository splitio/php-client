<?php
namespace SplitIO\Component\Cache\Storage\Adapter;

use SplitIO\Component\Common\Context;

/**
 * Class SafeRedisWrapper
 *
 * @package SplitIO\Component\Cache\Storage\Adapter
 */
class SafeRedisWrapper implements CacheStorageAdapterInterface
{
    private $cacheAdapter = null;

    /**
     * @param CacheStorageAdapterInterface $cacheAdapter
     */
    public function __construct(PRedis $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
    }

    /**
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        try {
            return $this->cacheAdapter->get($key);
        } catch (\Exception $e) {
            Context::getLogger()->critical("An error occurred getting " . $key . " from redis.");
            Context::getLogger()->critical($e->getMessage());
            Context::getLogger()->critical($e->getTraceAsString());
            return null;
        }
    }


    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys
     * An indexed array of keys of items to retrieve.
     *
     * @return array
     */
    public function fetchMany(array $keys = array())
    {
        try {
            return $this->cacheAdapter->fetchMany($keys);
        } catch (\Exception $e) {
            Context::getLogger()->critical("An error occurred getting " . json_encode($keys) . " from redis.");
            Context::getLogger()->critical($e->getMessage());
            Context::getLogger()->critical($e->getTraceAsString());
            return array();
        }
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function isOnList($key, $value)
    {
        try {
            return $this->cacheAdapter->isOnList($key, $value);
        } catch (\Exception $e) {
            Context::getLogger()->critical("An error occurred for " . $key);
            Context::getLogger()->critical($e->getMessage());
            Context::getLogger()->critical($e->getTraceAsString());
            return false;
        }
    }

    /**
     * @param $pattern
     * @return mixed|null
     */
    public function getKeys($pattern = '*')
    {
        try {
            return $this->cacheAdapter->getKeys($pattern);
        } catch (\Exception $e) {
            Context::getLogger()->critical("An error occurred getting " . $pattern);
            Context::getLogger()->critical($e->getMessage());
            Context::getLogger()->critical($e->getTraceAsString());
            return array();
        }
    }

    /**
     * @param $queueName
     * @param $item
     * @return number
     */
    public function rightPushQueue($queueName, $item)
    {
        try {
            return $this->cacheAdapter->rightPushQueue($queueName, $item);
        } catch (\Exception $e) {
            Context::getLogger()->critical("An error occurred performing RPUSH into " . $queueName);
            Context::getLogger()->critical($e->getMessage());
            Context::getLogger()->critical($e->getTraceAsString());
            return 0;
        }
    }

    /**
     * @param $key
     * @param $ttl
     * @return mixed
     */
    public function expireKey($key, $ttl)
    {
        try {
            return $this->cacheAdapter->expireKey($key, $ttl);
        } catch (\Exception $e) {
            Context::getLogger()->critical("An error occurred setting expiration for " . $key);
            Context::getLogger()->critical($e->getMessage());
            Context::getLogger()->critical($e->getTraceAsString());
            return false;
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function sMembers($key)
    {
        try {
            return $this->cacheAdapter->sMembers($key);
        } catch (\Exception $e) {
            Context::getLogger()->critical("An error occurred performing SMEMBERS for " . $key);
            Context::getLogger()->critical($e->getMessage());
            Context::getLogger()->critical($e->getTraceAsString());
            return array();
        }
    }
}
