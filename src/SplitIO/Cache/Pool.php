<?php
namespace SplitIO\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;
use SplitIO\Cache\Storage\Adapter\Memcached as MemcachedAdapter;
use SplitIO\Cache\Storage\Adapter\Redis as RedisAdapter;
use SplitIO\Cache\Storage\Adapter\Filesystem as FilesystemAdapter;
use SplitIO\Common\Di;

class Pool implements CacheItemPoolInterface
{
    /** Common functions for cache key */
    use CacheKeyTrait;

    /** @var null|\SplitIO\Cache\Storage\Adapter\CacheStorageAdapterInterface */
    private $adapter = null;

    /** @var array */
    private $deferred = [];

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $adapterName = (isset($options['adapter']['name'])) ? $options['adapter']['name'] : 'memcached';
        $adapterOptions = (isset($options['adapter']['options'])
                            && is_array($options['adapter']['options'])) ? $options['adapter']['options'] : [];

        switch ($adapterName) {
            case 'memcached':
                $this->adapter = new MemcachedAdapter($adapterOptions);
                break;
            case 'redis':
                $this->adapter = new RedisAdapter($adapterOptions);
                break;
            case 'filesystem':
                $this->adapter = new FilesystemAdapter($adapterOptions);
                break;
            default:
                $this->adapter = new FilesystemAdapter($adapterOptions);
                break;

        }
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
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return CacheItemInterface
     *   The corresponding Cache Item.
     */
    public function getItem($key)
    {
        $this->assertValidKey($key);
        Di::getInstance()->getLogger()->debug("Fetching item ** $key ** from cache");
        return $this->adapter->getItem($key);
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param array $keys
     * An indexed array of keys of items to retrieve.
     *
     * @throws InvalidArgumentException
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
        $items = array();

        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * Note: This method MAY avoid retrieving the cached value for performance reasons.
     * This could result in a race condition with CacheItemInterface::get(). To avoid
     * such situation use CacheItemInterface::isHit() instead.
     *
     * @param string $key
     *    The key for which to check existence.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *  True if item exists in the cache, false otherwise.
     */
    public function hasItem($key)
    {
        $this->assertValidKey($key);

        $item = $this->getItem($key);
        return $item->isHit();
    }

    /**
     * Deletes all items in the pool.
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear()
    {
        return $this->adapter->clear();
    }

    /**
     * Removes the item from the pool.
     *
     * @param string $key
     *   The key for which to delete
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem($key)
    {
        $this->assertValidKey($key);

        return $this->adapter->deleteItem($key);
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param array $keys
     *   An array of keys that should be removed from the pool.

     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->assertValidKey($key);
        }

        return $this->adapter->deleteItems($keys);
    }

    /**
     * Persists a cache item immediately.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(CacheItemInterface $item)
    {
        $key = $item->getKey();
        $value = $item->get();

        //PSR-6 CacheItemInterface doesn't define a method to get the item expiration value.
        $expiration = (method_exists($item, 'getExpiration')) ? $item->getExpiration() : 0;

        if ($this->adapter->save($key, $value, $expiration)) {
            Di::getInstance()->getLogger()->debug("Saving cache item: $key - $value - $expiration");
            return true;
        }

        return false;
    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param CacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[] = $item;
        return true;
    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit()
    {
        $success = true;

        foreach ($this->deferred as $item) {
            if (! $this->save($item)) {
                $success = false;
            }
        }

        if ($success) {
            $this->deferred = [];
        }

        return $success;
    }


    public function saveItemOnList($key, $value)
    {
        return $this->adapter->addItemList($key, $value);
    }

    public function removeItemOnList($key, $value)
    {
        return $this->adapter->removeItemList($key, $value);
    }

    public function isItemOnList($key, $value)
    {
        return $this->adapter->isOnList($key, $value);
    }

    public function getItemsOnList($key)
    {
        return $this->adapter->getListItems($key);
    }

    public function getKeys($pattern = '*')
    {
        return $this->adapter->getKeys($pattern);
    }
}
