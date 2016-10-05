<?php
namespace SplitIO\Component\Cache\Storage\Adapter;

use SplitIO\Component\Cache\Storage\Exception\AdapterException;
use SplitIO\Component\Cache\Item;

/**
 * Class Redis
 * @package SplitIO\Component\Cache\Storage\Adapter
 */
class Redis implements CacheStorageAdapterInterface
{

    /** Default Redis host */
    const DEFAULT_HOST = 'localhost';

    /** Default Redis port */
    const DEFAULT_PORT = 6379;

    /** Default Redis timeout */
    const DEFAULT_TIMEOUT = 30;

    /** Default value time to live */
    const DEFAULT_VALUE_TTL = 60;

    /** @var \Redis|null  */
    private $client = null;

    /** @var array */
    private $options = array(
        'host'      => self::DEFAULT_HOST,
        'port'      => self::DEFAULT_PORT,
        'timeout'   => self::DEFAULT_TIMEOUT,
        'ttl'       => self::DEFAULT_VALUE_TTL,
        'password'  => false
    );

    /**
     * @param array $options
     * @throws AdapterException
     */
    public function __construct(array $options)
    {
        if (!extension_loaded('redis')) {
            throw new AdapterException("Redis extension is not loaded");
        }

        $host = (isset($options['host'])) ? $options['host'] : self::DEFAULT_HOST;
        $port = (isset($options['port'])) ? $options['port'] : self::DEFAULT_PORT;
        $timeout = (isset($options['timeout'])) ? (float) $options['timeout'] : self::DEFAULT_TIMEOUT;
        $password = (isset($options['password'])) ? $options['password'] : false;

        $this->client = new \Redis();

        if (! $this->client->connect($host, $port, $timeout)) {
            throw new AdapterException("Redis servers cannot be connected");
        }

        if ($password !== false) {
            $this->client->auth($password);
        }

        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param string $key
     * @return \SplitIO\Component\Cache\Item
     */
    public function getItem($key)
    {
        $item = new Item($key);

        $redisItem = $this->client->get($key);

        if ($redisItem !== false) {
            $item->set($redisItem);
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
        /*
        if ($expiration === 0 || $expiration === null) {
            $expirationToSet = $this->options['ttl'];
        } else {
            $expirationToSet = $expiration - time();
        }

        return $this->client->setex($key, $expirationToSet, serialize($value));
        */

        return $this->client->set($key, $value);
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
