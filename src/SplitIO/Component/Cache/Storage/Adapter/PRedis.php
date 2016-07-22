<?php
namespace SplitIO\Component\Cache\Storage\Adapter;

use SplitIO\Component\Cache\Storage\Exception\AdapterException;
use SplitIO\Component\Cache\Item;

/**
 * Class PRedis
 * @package SplitIO\Component\Cache\Storage\Adapter
 */
class PRedis implements CacheStorageAdapterInterface
{
    /** @var \Predis\Client|null  */
    private $client = null;

    /**
     * @param array $options
     * @throws AdapterException
     */
    public function __construct(array $options)
    {
        if (!class_exists('\Predis\Client')) {
            throw new AdapterException("PRedis class is not loaded");
        }

        $_parameters = (isset($options['parameters'])) ? $options['parameters'] : null;
        $_options = (isset($options['options'])) ? $options['options'] : null;

        $this->client = new \Predis\Client($_parameters, $_options);
    }

    /**
     * @param string $key
     * @return \SplitIO\Component\Cache\Item
     */
    public function getItem($key)
    {
        $item = new Item($key);

        $redisItem = $this->client->get($key);

        if ($redisItem !== null) {
            $item->set(unserialize($redisItem));
        }

        return $item;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|null $expiration
     * @return bool
     */
    public function addItem($key, $value, $expiration = null)
    {
        return $this->save($key, $value, $expiration);
    }

    /**
     * @return bool
     */
    public function clear()
    {
        return $this->client->flushAll();
    }

    /**
     * @param $key
     * @return bool
     */
    public function deleteItem($key)
    {
        $return = $this->client->del($key);

        if ($return > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function deleteItems(array $keys)
    {
        $return = $this->client->del($keys);

        if ($return > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $expiration
     * @return bool
     */
    public function save($key, $value, $expiration = null)
    {
        return $this->client->set($key, serialize($value));
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
        return $this->client->sAdd($key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function removeItemList($key, $value)
    {
        return $this->client->sRem($key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function isOnList($key, $value)
    {
        return $this->client->sIsMember($key, $value);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getListItems($key)
    {
        return $this->client->sMembers($key);
    }

    public function getKeys($pattern = '*')
    {
        return $this->client->keys($pattern);
    }

    public function incrementKey($key)
    {
        return $this->client->incr($key);
    }

    public function getSet($key, $value)
    {
        return $this->client->getSet($key, $value);
    }
}
