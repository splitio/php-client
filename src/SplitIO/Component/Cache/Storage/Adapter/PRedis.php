<?php
namespace SplitIO\Component\Cache\Storage\Adapter;

use SplitIO\Component\Cache\Storage\Exception\AdapterException;
use SplitIO\Component\Cache\Item;
use SplitIO\Component\Utils as SplitIOUtils;
use SplitIO\Component\Common\Di;

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
        $_redisConfig = $this->getRedisConfiguration($options);

        $this->client = new \Predis\Client($_redisConfig['redis'], $_redisConfig['options']);
    }

    private function isValidSentinelConfig($sentinels, $options)
    {
        if (!is_array($sentinels)) {
            throw new AdapterException('sentinels must be an array.');
        }
        if (count($sentinels) == 0) {
            throw new AdapterException('At least one sentinel is required.');
        }
        if (SplitIOUtils\isAssociativeArray($sentinels)) {
            throw new AdapterException('sentinels must not be an associative array.');
        } else {
            if (!isset($options['replication']) && !isset($options['distributedStrategy'])) {
                throw new AdapterException("Missing 'distributedStrategy' in options.");
            }
            if (!isset($options['replication']) && isset($options['distributedStrategy']) &&
                $options['distributedStrategy'] != 'sentinel') {
                throw new AdapterException("Wrong configuration of redis 'distributedStrategy'.");
            }
            if (isset($options['replication']) && !isset($options['distributedStrategy'])) {
                Di::getLogger()->warning("'replication' option was deprecated please use 'distributedStrategy'");
                if ($options['replication'] != 'sentinel') {
                    throw new AdapterException('Wrong configuration of redis replication.');
                } else {
                    $options['distributedStrategy'] = 'sentinel';
                }
            }
            if (!isset($options['service'])) {
                throw new AdapterException('Master name is required in replication mode for sentinel.');
            }
        }
        return true;
    }

    private function isValidClusterConfig($clusters, $options)
    {
        if (!is_array($clusters)) {
            throw new AdapterException('clusterNodes must be an array.');
        }
        if (count($clusters) == 0) {
            throw new AdapterException('At least one node is required.');
        }
        if (SplitIOUtils\isAssociativeArray($clusters)) {
            throw new AdapterException('clusterNodes must not be an associative array.');
        }
        return true;
    }

    private function getRedisConfiguration($options)
    {
        $redisConfigutation = array(
            'redis' => null,
            'options' => null
        );
        
        $parameters = (isset($options['parameters'])) ? $options['parameters'] : null;
        $sentinels = (isset($options['sentinels'])) ? $options['sentinels'] : null;
        $clusters = (isset($options['clusterNodes'])) ? $options['clusterNodes'] : null;
        $_options = (isset($options['options'])) ? $options['options'] : null;

        if ($_options && isset($_options['prefix'])) {
            $_options['prefix'] = self::normalizePrefix($_options['prefix']);
        }

        if (isset($parameters)) {
            $redisConfigutation['redis'] = $parameters;
        } else {
            // @TODO change statements when we deprecate replication => sentinel
            if (isset($sentinels) && $this->isValidSentinelConfig($sentinels, $_options)) {
                $_options['replication'] = 'sentinel';
                $redisConfigutation['redis'] = $sentinels;
            }
            if (isset($_options['distributedStrategy']) && $_options['distributedStrategy'] == 'cluster' &&
                $this->isValidClusterConfig($clusters, $_options)) {
                $_options['cluster'] = 'redis';
                $redisConfigutation['redis'] = $clusters;
            }
        }
        $redisConfigutation['options'] = $_options;
        return $redisConfigutation;
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

    public function getListItemsRandomly($key, $count)
    {
        return $this->client->srandmember($key, $count);
    }

    public function getKeys($pattern = '*')
    {
        $prefix = null;
        if ($this->client->getOptions()->__isset("prefix")) {
            $prefix = $this->client->getOptions()->__get("prefix")->getPrefix();
        }

        $keys = $this->client->keys($pattern);

        if ($prefix) {
            if (is_array($keys)) {
                for ($i=0; $i < count($keys); $i++) {
                    $keys[$i] = str_replace($prefix, '', $keys[$i]);
                }
            } else {
                $keys = str_replace($prefix, '', $keys);
            }
        }

        return $keys;
    }

    public function incrementKey($key)
    {
        return $this->client->incr($key);
    }

    public function getSet($key, $value)
    {
        return $this->client->getSet($key, $value);
    }

    private static function normalizePrefix($prefix)
    {
        if ($prefix && strlen($prefix)) {
            if ($prefix[strlen($prefix) - 1] == '.') {
                return $prefix;
            } else {
                return $prefix.'.';
            }
        } else {
            return null;
        }
    }

    public function rightPushQueue($queueName, $item)
    {
        if (!is_array($item)) {
            return (boolean) $this->client->rpush($queueName, array($item));
        } else {
            return (boolean) $this->client->rpush($queueName, $item);
        }
    }
}
