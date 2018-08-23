<?php

namespace SplitIO\Test\Suite\Redis;

use SplitIO\Component\Cache\Storage\Adapter\PRedis;

class PRedisReadOnlyMock
{
    private $predis = null;

    public function __construct(PRedis $predis)
    {
        $this->predis = $predis;
    }


 /**
     * @param string $key
     * @return \SplitIO\Component\Cache\Item
     */
    public function getItem($key)
    {
        return $this->predis->getItem($key);
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
        return $this->predis->getItems($keys);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $expiration
     * @return bool
     */
    public function addItem($key, $value, $expiration = null)
    {
        throw new \Exception('READONLY mode mocked.');
    }

    /**
     * @return bool
     */
    public function clear()
    {
        throw new \Exception('READONLY mode mocked.');
    }

    /**
     * @param $key
     * @return bool
     */
    public function deleteItem($key)
    {
        throw new \Exception('READONLY mode mocked.');
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function deleteItems(array $keys)
    {
        throw new \Exception('READONLY mode mocked.');
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $expiration
     * @return bool
     */
    public function save($key, $value, $expiration = null)
    {
        throw new \Exception('READONLY mode mocked.');
    }

    /**
     * Adds a values to the set value stored at key.
     * If this value is already in the set, FALSE is returned.
     *
     * @param $key
     * @param $value
     * @return boolean
     */
    public function addItemList($key, $value)
    {
        throw new \Exception('READONLY mode mocked.');
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function removeItemList($key, $value)
    {
        throw new \Exception('READONLY mode mocked.');
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function isOnList($key, $value)
    {
        return $this->predis->isOnList($key, $value);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getListItems($key)
    {
        return $this->predis->getListItems($key);
    }

    public function getListItemsRandomly($key, $count)
    {
        return $this->predis->getListItemsRandomly($key, $count);
    }

    public function getKeys($pattern = '*')
    {
        return $this->predis->getKeys($pattern);
    }

    public function incrementKey($key)
    {
        throw new \Exception('READONLY mode mocked.');
    }

    public function getSet($key, $value)
    {
        return $this->predis->getSet($key, $value);
    }

    private static function normalizePrefix($prefix)
    {
        return $this->predis->normalizePrefix($prefix);
    }

    public function rightPushQueue($queueName, $item)
    {
        throw new \Exception('READONLY mode mocked.');
    }

    public function saveItemOnList($key, $value)
    {
        throw new \Exception('READONLY mode mocked.');
    }
}
