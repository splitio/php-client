<?php
namespace SplitIO\Cache\Storage\Adapter;

use SplitIO\Cache\Storage\Exception\AdapterException;
use SplitIO\Cache\Item;

/**
 * Class Memcached
 * @package SplitIO\Cache\Storage\Adapter
 */
class Memcached implements CacheStorageAdapterInterface
{

    /** Default Memcached host */
    const DEFAULT_HOST = 'localhost';

    /** Default Memcached port */
    const DEFAULT_PORT = 11211;

    /** Default value time to live */
    const DEFAULT_VALUE_TTL = 60;

    /** @var \Memcached|null  */
    private $client = null;

    /** @var array */
    private $options = [
        'servers'   =>[[self::DEFAULT_HOST, self::DEFAULT_PORT]],
        'ttl'       => self::DEFAULT_VALUE_TTL
    ];

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        //List of servers in [] = array(string host, integer port)
        $servers = (isset($options['servers'])) ? $options['servers'] : null;

        $this->client = new \Memcached();
        if (is_array($servers) && !empty($servers)) {
            if (! $this->client->addServers($servers)) {
                throw new AdapterException("Memcached servers cannot be added");
            }
        } else {
            if (! $this->client->addServer(self::DEFAULT_HOST, self::DEFAULT_PORT)) {
                throw new AdapterException("Memcached default server cannot be added");
            }
        }

        $this->options = array_merge($this->options, $options);
    }

    /**
     * @param string $key
     * @return \Psr\Cache\CacheItemInterface
     */
    public function getItem($key)
    {
        $item = new Item($key);

        /**
         * Returns the value stored in the cache or FALSE otherwise.
         * The Memcached::getResultCode() will return Memcached::RES_NOTFOUND if the key does not exist.
         */
        $memcachedItem = $this->client->get($key);

        if ($this->client->getResultCode() !== \Memcached::RES_NOTFOUND && is_string($memcachedItem)) {
            $item->set(unserialize($memcachedItem));
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
        return $this->client->flush();
    }

    /**
     * @param $key
     * @return bool
     */
    public function deleteItem($key)
    {
        return $this->client->delete($key);
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function deleteItems(array $keys)
    {
        return $this->client->deleteMulti($keys);
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $expiration
     * @return bool
     */
    public function save($key, $value, $expiration = null)
    {
        if ($expiration === 0 || $expiration === null) {
            $expirationToSet = $this->options['ttl'];
        } else {
            $expirationToSet = $expiration - time();
        }

        return $this->client->set($key, serialize($value), $expirationToSet);
    }


}