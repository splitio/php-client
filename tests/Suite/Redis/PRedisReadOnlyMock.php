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
     * @param $key
     * @param $value
     * @return mixed
     */
    public function isOnList($key, $value)
    {
        return $this->predis->isOnList($key, $value);
    }

    public function getKeys($pattern = '*')
    {
        return $this->predis->getKeys($pattern);
    }

    private function normalizePrefix($prefix)
    {
        return $this->predis->normalizePrefix($prefix);
    }

    public function rightPushInList($key, $value)
    {
        throw new \Exception('READONLY mode mocked.');
    }
}
