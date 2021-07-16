<?php

require_once __DIR__ . '/../vendor/autoload.php';
define('LOG_ADAPTER', '');

class VoidStaticCache extends \SplitIO\Component\Cache\StaticCache
{
    public function add($key, $value) { return $value; }
    public function has($key) { return false; }
    public function get($key, $alternative = null) { return $alternative; }
    public function remove($key) { }
    public function flush() { }
}

class RedisMock
{
    private static $cache = [];
    private static $sets = [];

    public function rPush($key, $value)
    {

        $values = func_get_args();
        $key = array_shift($values);
        self::$sets[$key] = self::$sets[$key] ?? [];
        self::$sets[$key] = self::$sets[$key] + $values;
        return count(self::$sets[$key]);
    }

    public function sAdd($key, $value)
    {
        $values = func_get_args();
        $key = array_shift($values);
        self::$sets[$key] = self::$sets[$key] ?? [];
        self::$sets[$key] = self::$sets[$key] + $values;
        return count($values);
    }

    public function sRem($key, $value)
    {
        $values = func_get_args();
        $key = array_shift($values);
        self::$sets[$key] = self::$sets[$key] ?? [];
        $count = 0;
        foreach(self::$sets[$key] as $idx => $value) {
            if (in_array($value, $values)) {
                unset(self::$sets[$key][$idx]);
                $count++;
            }
        }
        return $count;
    }

    public function set($key, $value)
    {
        self::$cache[$key] = $value;
        return true;
    }

    public function get($key)
    {
        return self::$cache[$key] ?? false;
    }

    public function del($key)
    {
        unset(self::$cache[$key]);
        return true;
    }
}