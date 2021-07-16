<?php

namespace SplitIO\Component\Cache;

/**
 * Intention of this class is to avoid processing the data.
 * Especially if SDK is being used inside of loops.
 */
class StaticCache
{
    /**
     * @var array $cache
     */
    private static $cache = [];

    /**
     * @param string $key
     * @param mixed $value
     */
    public function add($key, $value)
    {
        return self::$cache[$key] = $value;
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return array_key_exists($key, self::$cache);
    }

    /**
     * @param string $key
     * @param mixed $alternative
     * @return mixed
     */
    public function get($key, $alternative = null)
    {
        return self::$cache[$key] ?? $alternative;
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        if ($this->has($key)) {
            unset(self::$cache[$key]);
        }
    }

    /**
     * 
     */
    public function flush()
    {
        self::$cache = [];
    }
}
