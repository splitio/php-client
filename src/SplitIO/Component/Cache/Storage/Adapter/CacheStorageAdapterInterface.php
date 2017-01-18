<?php
namespace SplitIO\Component\Cache\Storage\Adapter;

interface CacheStorageAdapterInterface
{

    public function __construct(array $options);

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
     * @param string $key
     * @param mixed $value
     * @param int|null $expiration
     * @return bool
     */
    public function addItem($key, $value, $expiration = null);


    /**
     * @return bool
     */
    public function clear();

    /**
    * @param $key
    * @return bool
    */
    public function deleteItem($key);

    /**
     * @param array $keys
     * @return bool
     */
    public function deleteItems(array $keys);

    /**
     * @param $key
     * @param $value
     * @param int|null $expiration
     * @return bool
     */
    public function save($key, $value, $expiration = null);

    /**
     * Adds a values to the set value stored at key.
     * If this value is already in the set, FALSE is returned.
     *
     * @param $key
     * @param $value
     * @return boolean
     */
    public function addItemList($key, $value);

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function removeItemList($key, $value);

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function isOnList($key, $value);

    /**
     * @param $key
     * @return mixed
     */
    public function getListItems($key);

    /**
     * @param $key
     * @return int
     */
    public function incrementKey($key);

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function getSet($key, $value);
}
