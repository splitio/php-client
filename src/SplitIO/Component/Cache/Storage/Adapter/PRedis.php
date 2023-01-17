<?php
namespace SplitIO\Component\Cache\Storage\Adapter;

use SplitIO\Component\Cache\Storage\Exception\AdapterException;
use SplitIO\Component\Cache\Item;
use SplitIO\Component\Utils as SplitIOUtils;
use SplitIO\Component\Common\Context;

/**
 * Class PRedis
 *
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

    /**
     * @param array $nodes
     * @param string $type
     * @return string|null
     */
    private function isValidConfigArray($nodes, $type)
    {
        if (!is_array($nodes)) {
            return $type . "s must be an array.";
        }
        if (empty($nodes)) {
            return "At least one " . $type . " is required.";
        }
        if (SplitIOUtils\isAssociativeArray($nodes)) {
            return $type . "s must not be an associative array.";
        }
        return null;
    }

    /**
     * @param array $sentinels
     * @param array $options
     * @return bool
     * @throws AdapterException
     */
    private function isValidSentinelConfig($sentinels, $options)
    {
        $msg = $this->isValidConfigArray($sentinels, 'sentinel');
        if (!is_null($msg)) {
            throw new AdapterException($msg);
        }
        if (!isset($options['service'])) {
            throw new AdapterException('Master name is required in replication mode for sentinel.');
        }
        return true;
    }

    private function validateKeyHashTag($keyHashTag)
    {
        if (!is_string($keyHashTag)) {
            return array('valid' => false, 'msg' => 'keyHashTag must be string.');
        }
        if ((strlen($keyHashTag) < 3) || ($keyHashTag[0] != "{") ||
            (substr($keyHashTag, -1) != "}") || (substr_count($keyHashTag, "{") != 1) ||
            (substr_count($keyHashTag, "}") != 1)) {
            return array('valid' => false, 'msg' => 'keyHashTag is not valid.');
        }
        return array('valid' => true, 'msg' => '');
    }

    /**
     * @param mixed $options
     * @return string
     * @throws AdapterException
     */
    private function getDefaultKeyHashTag($options)
    {
        if (!isset($options['keyHashTag'])) {
            return "{SPLITIO}";
        }
        $validation = $this->validateKeyHashTag($options['keyHashTag']);
        if (!($validation['valid'])) {
            throw new AdapterException($validation['msg']);
        }
        return $options['keyHashTag'];
    }


    /**
     * @param mixed $options
     * @return string
     * @throws AdapterException
     */
    private function selectKeyHashTag($options)
    {
        if (!isset($options['keyHashTags'])) { // check if array keyHashTags is set
            return $this->getDefaultKeyHashTag($options); // defaulting to keyHashTag or {SPLITIO}
        }
        $keyHashTags = $options['keyHashTags'];
        $msg = $this->isValidConfigArray($keyHashTags, 'keyHashTags'); // check if is valid array
        if (!is_null($msg)) {
            throw new AdapterException($msg);
        }
        $filteredArray = array_filter( // filter to only use string element {X}
            $keyHashTags,
            function ($value) {
                return $this->validateKeyHashTag($value)['valid'];
            }
        );
        if (empty($filteredArray)) {
            throw new AdapterException('keyHashTags size is zero after filtering valid elements.');
        }
        return $selected = $filteredArray[array_rand($filteredArray, 1)];
    }


    /**
     * @param array $clusters
     * @return bool
     * @throws AdapterException
     */
    private function isValidClusterConfig($clusters)
    {
        $msg = $this->isValidConfigArray($clusters, 'clusterNode');
        if (!is_null($msg)) {
            throw new AdapterException($msg);
        }
        return true;
    }

    /**
     * @param mixed $options
     * @return array
     * @throws AdapterException
     */
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

        if (isset($_options['distributedStrategy']) && isset($parameters['tls'])) {
            throw new AdapterException("SSL/TLS cannot be used together with sentinel/cluster yet");
        }

        if ($_options && isset($_options['prefix'])) {
            $_options['prefix'] = self::normalizePrefix($_options['prefix']);
        }

        if (isset($parameters)) {
            $redisConfigutation['redis'] = $parameters;
        } else {
            // @TODO remove this statement when replication will be deprecated
            if (isset($_options['replication'])) {
                Context::getLogger()->warning("'replication' option was deprecated please use 'distributedStrategy'");
                if (!isset($_options['distributedStrategy'])) {
                    $_options['distributedStrategy'] = $_options['replication'];
                }
            }
            if (isset($_options['distributedStrategy'])) {
                switch ($_options['distributedStrategy']) {
                    case 'cluster':
                        if ($this->isValidClusterConfig($clusters)) {
                            $keyHashTag = $this->selectKeyHashTag($_options);
                            $_options['cluster'] = 'redis';
                            $redisConfigutation['redis'] = $clusters;
                            $prefix = isset($_options['prefix']) ? $_options['prefix'] : '';
                            $_options['prefix'] = $keyHashTag . $prefix;
                        }
                        break;
                    case 'sentinel':
                        if ($this->isValidSentinelConfig($sentinels, $_options)) {
                            $_options['replication'] = 'sentinel';
                            $redisConfigutation['redis'] = $sentinels;
                        }
                        break;
                    default:
                        throw new AdapterException("Wrong configuration of redis 'distributedStrategy'.");
                }
            } else {
                throw new AdapterException("Wrong configuration of redis.");
            }
        }
        $redisConfigutation['options'] = $_options;
        return $redisConfigutation;
    }

    /**
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        return $this->client->get($key);
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
     * @return array
     */
    public function fetchMany(array $keys = array())
    {
        $toReturn = array();
        if (empty($keys)) {
            return $toReturn;
        }
        $values = $this->client->mget($keys);
        foreach ($keys as $index => $key) {
            $toReturn[$key] = $values[$index];
        }
        return $toReturn;
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

    public function getKeys($pattern = '*')
    {
        $prefix = null;
        if ($this->client->getOptions()->__isset("prefix")) {
            $prefix = $this->client->getOptions()->__get("prefix")->getPrefix();
        }

        if ($this->client->getOptions()->__isset("distributedStrategy") &&
            $this->client->getOptions()->__get("distributedStrategy") == "cluster") {
            $keys = array();
            foreach ($this->client as $nodeClient) {
                $nodeClientKeys = $nodeClient->keys($pattern);
                $keys = array_merge($keys, $nodeClientKeys);
            }
        } else {
            $keys = $this->client->keys($pattern);
        }
        if ($prefix) {
            if (is_array($keys)) {
                foreach ($keys as $index => $key) {
                    $keys[$index] = str_replace($prefix, '', $key);
                }
            } else {
                $keys = str_replace($prefix, '', $keys);
            }
        }
        return $keys;
    }

    private static function normalizePrefix($prefix)
    {
        if ($prefix && is_string($prefix) && strlen($prefix)) {
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
