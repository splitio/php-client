<?php
namespace SplitIO\Component\Cache\Storage\Adapter;

interface CacheStorageAdapterInterface
{

    // public function __construct(array $options);

    /**
     * @param null|string $pattern
     * @return mixed
     */
    public function getKeys($pattern = '*');

    /**
     * @param string $key
     * @return \SplitIO\Component\Cache\Item
     */
    public function getItem($key);

    public function getItems(array $keys);

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function isOnList($key, $value);

    /**
     * @param $queueName
     * @param $item
     * @return integer
     */
    public function rightPushQueue($queueName, $item);

    /**
     * @param $key
     * @param $ttl
     * @return boolean
     */
    public function expireKey($key, $ttl);
}
