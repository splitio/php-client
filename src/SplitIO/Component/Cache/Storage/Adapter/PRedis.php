<?php
namespace SplitIO\Component\Cache\Storage\Adapter;

use SplitIO\Component\Cache\Storage\Exception\AdapterException;
use SplitIO\Component\Utils as SplitIOUtils;

/**
 * Class PRedis
 *
 * @package SplitIO\Component\Cache\Storage\Adapter
 */
class PRedis implements CacheStorageAdapterInterface
{
    /** @var \Predis\Client|null  */
    private $client = null;

    /** @var string  */
    private $prefix = "";

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

        $this->client = new \Predis\Client($_redisConfig['parameters'], $_redisConfig['options']);

        if (isset($_redisConfig['options']['prefix'])) {
            $this->prefix = $_redisConfig['options']['prefix'];
        }
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
        $msg = $this->isValidConfigArray($keyHashTags, 'keyHashTag'); // check if is valid array
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
        return $filteredArray[array_rand($filteredArray, 1)];
    }


    /**
     * @param mixed $options
     * @return array
     */
    private function getRedisConfiguration($options)
    {
        $redisConfigutation = array(
            'parameters' => (isset($options['parameters'])) ? $options['parameters'] : null,
            'options' => null,
        );

        $_options = (isset($options['options'])) ? $options['options'] : null;
        if ($_options && isset($_options['prefix'])) {
            $_options['prefix'] = self::normalizePrefix($_options['prefix']);
        }

        if (isset($_options['cluster'])) {
            $keyHashTag = $this->selectKeyHashTag($_options);
            $prefix = isset($_options['prefix']) ? $_options['prefix'] : '';
            $_options['prefix'] = $keyHashTag . $prefix;
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

    /**
     * @param string $key
     * @return mixed
     */
    public function sMembers($key)
    {
        return $this->client->smembers($key);
    }

    public function getKeys($pattern = '*')
    {
        $keys = $this->client->keys($pattern);
        return str_replace($this->prefix, '', $keys);
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
