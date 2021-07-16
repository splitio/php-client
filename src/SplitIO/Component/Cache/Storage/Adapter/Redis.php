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
        'host'          => self::DEFAULT_HOST,
        'port'          => self::DEFAULT_PORT,
        'timeout'       => self::DEFAULT_TIMEOUT,
        'ttl'           => self::DEFAULT_VALUE_TTL,
        'password'      => false,
        'serializer'    => 0, // \Redis::SERIALIZER_NONE
        'cluster'       => array(
            'alias'     => null,
            'nodes'     => null,
            'persistant'=> false
        ),
        'sentinel'      => array(
            'host'          => null,
            'port'          => null,
        )
    );

    /**
     * @param array $options
     * @throws AdapterException
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        if (!empty($options['client'])) {
            $this->client = $options['client'];
            return;
        }

        $this->connect($options);
    }

    /**
     * @param array $options
     * @throws AdapterException
     */
    private function connect($options)
    {
        if (!extension_loaded('redis')) {
            throw new AdapterException("Redis extension is not loaded");
        }
        if (array_key_exists('cluster', $options)) {
            $this->connectCluster($options);
        } elseif (array_key_exists('sentinel', $options)) {
            $this->connectSentinel($options);
        } elseif (array_key_exists('host', $options)) {
            $this->connectNode($options);
        } else {
            throw new AdapterException("Wrong redis configuration");
        }
        $this->configureConnection($options);
    }

    /**
     * @param array $options
     * @throws AdapterException
     */
    private function connectNode($options)
    {
        $host = $options['host'] ?? null;
        $port = $options['port'] ?? 6379;
        $timeout = floatval($options['timeout'] ?? self::DEFAULT_TIMEOUT);
        $readTimeout = floatval($options['read_timeout'] ?? 0);
        $this->client = new \Redis();
        if (!$this->client->connect($host, $port, $timeout, null, null, $readTimeout)) {
            throw new AdapterException("Redis servers cannot be connected");
        }
    }

    /**
     * @param array $options
     * @throws AdapterException
     */
    private function connectCluster($options)
    {
        if (!class_exists('RedisCluster')) {
            throw new AdapterException("RedisCluster is not installed");
        }
        $cluster_options = $options['cluster'] ?? [];
        $alias = $cluster_options['alias'] ?? null;
        $nodes = $cluster_options['nodes'] ?? null;
        $timeout = floatval($options['timeout'] ?? self::DEFAULT_TIMEOUT);
        $readTimeout = floatval($options['read_timeout'] ?? 0);
        $persistant = $cluster_options['persistant'] ?? false;

        $this->client = new \RedisCluster(
            $alias,
            $this->splitNodes($nodes),
            $timeout,
            $readTimeout,
            $persistant
        );
        if (!$this->client) {
            throw new AdapterException("Redis cluster cannot be connected");
        }
    }

    /**
     * @param array $options
     * @throws AdapterException
     */
    private function connectSentinel($options)
    {
        if (!class_exists('RedisCluster')) {
            throw new AdapterException("RedisSentinel is not installed");
        }
        $sentinel_options = $options['sentinel'] ?? [];
        $host = $sentinel_options['host'] ?? null;
        $port = $sentinel_options['port'] ?? null;
        $timeout = floatval($options['timeout'] ?? self::DEFAULT_TIMEOUT);
        $readTimeout = floatval($options['read_timeout'] ?? 0);
        $persistant = $cluster_options['persistant'] ?? false;

        $this->client = new \RedisSentinel(
            $host,
            $port,
            $timeout,
            $persistant,
            null,
            $readTimeout
        );
        if (!$this->client) {
            throw new AdapterException("Redis Sentinel host cannot be connected");
        }
    }

    /**
     * @param string $nodes
     * @return array
     */
    private function splitNodes($nodes)
    {
        return preg_split('#\s|,|\|;#', $nodes);
    }

    /**
     * @param array $options
     */
    private function configureConnection($options)
    {
        $auth = $options['auth'] ?? $options['password'] ?? null;
        $serializer = $options['serializer'] ?? \Redis::SERIALIZER_NONE;
        $prefix = $options['prefix'] ?? null;

        if ($this->client instanceof \RedisCluster) {
            $this->client->setOption(\RedisCluster::OPT_SLAVE_FAILOVER, \RedisCluster::FAILOVER_DISTRIBUTE);
        }

        $this->client->setOption(\Redis::OPT_SERIALIZER, $serializer);

        if ($prefix) {
            $this->client->setOption(\Redis::OPT_PREFIX, $prefix .'.');
        }
        
        if ($auth) {
            $this->client->auth($auth);
        }
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
        return $this->client->mget($keys);
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
        if ($this->client instanceof \RedisCluster) {
            foreach ($this->client->_masters() as $node) {
                $this->client->flushAll($node);
            }
        } else {
            return $this->client->flushAll();
        }
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

    public function rightPushQueue($queueName, $item)
    {
        if (!is_array($item)) {
            return $this->client->rpush($queueName, array($item));
        } else {
            return $this->client->rpush($queueName, $item);
        }
    }

    public function expireKey($key, $ttl)
    {
        return $this->client->expire($key, $ttl);
    }
}
