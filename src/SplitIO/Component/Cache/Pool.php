<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Cache\Storage\Adapter\PRedis as PRedisAdapter;
use SplitIO\Component\Cache\Storage\Adapter\SafeRedisWrapper;
use SplitIO\Component\Common\Di;

class Pool extends CacheKeyTrait
{
    /** @var null|\SplitIO\Component\Cache\Storage\Adapter\CacheStorageAdapterInterface */
    private $adapter = null;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $adapterOptions = (isset($options['adapter']['options'])
            && is_array($options['adapter']['options'])) ? $options['adapter']['options'] : array();

        $this->adapter = new SafeRedisWrapper(new PredisAdapter($adapterOptions));
    }

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws \InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return \SplitIO\Component\Cache\Item
     *   The corresponding Cache Item.
     */
    public function getItem($key)
    {
        $this->assertValidKey($key);
        Di::getLogger()->debug("Fetching item ** $key ** from cache");
        return $this->adapter->getItem($key);
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys
     * An indexed array of keys of items to retrieve.
     *
     * @throws \InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return array|\Traversable
     *   A traversable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = array())
    {
        return $this->adapter->getItems($keys);
    }

    public function isItemOnList($key, $value)
    {
        return $this->adapter->isOnList($key, $value);
    }

    public function getKeys($pattern = '*')
    {
        return $this->adapter->getKeys($pattern);
    }

    public function rightPushInList($queue, $item)
    {
        return $this->adapter->rightPushQueue($queue, $item);
    }

    public function expireKey($key, $ttl)
    {
        return $this->adapter->expireKey($key, $ttl);
    }
}
