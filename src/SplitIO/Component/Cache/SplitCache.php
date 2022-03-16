<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;

class SplitCache implements SplitCacheInterface
{
    const KEY_TILL_CACHED_ITEM = 'SPLITIO.splits.till';

    const KEY_SPLIT_CACHED_ITEM = 'SPLITIO.split.{splitName}';

    const KEY_TRAFFIC_TYPE_CACHED = 'SPLITIO.trafficType.{trafficTypeName}';

    private static function getCacheKeyForSinceParameter()
    {
        return self::KEY_TILL_CACHED_ITEM;
    }

    private static function getCacheKeySearchPattern()
    {
        return self::getCacheKeyForSplit('*');
    }

    private static function getCacheKeyForSplit($splitName)
    {
        return str_replace('{splitName}', $splitName, self::KEY_SPLIT_CACHED_ITEM);
    }

    private static function getSplitNameFromCacheKey($key)
    {
        $cacheKeyPrefix = self::getCacheKeyForSplit('');
        return str_replace($cacheKeyPrefix, '', $key);
    }

    /**
     * @return long
     */
    public function getChangeNumber()
    {
        $since = Di::getCache()->getItem(self::getCacheKeyForSinceParameter())->get();
        // empty check for nullable value
        return (empty($since)) ? -1 : $since;
    }

    /**
     * @param string $splitName
     * @return string JSON representation
     */
    public function getSplit($splitName)
    {
        $cache = Di::getCache();
        $cacheItem = $cache->getItem(self::getCacheKeyForSplit($splitName));
        return $cacheItem->get();
    }

    /**
     * @param array $splitNames
     * @return string JSON representation
     */
    public function getSplits($splitNames)
    {
        $cache = Di::getCache();
        $cacheItems = $cache->getItems(array_map('self::getCacheKeyForSplit', $splitNames));
        $toReturn = array();
        foreach ($cacheItems as $key => $value) {
            $toReturn[self::getSplitNameFromCacheKey($key)] = $value->get();
        }
        return $toReturn;
    }

    /**
     * @return array(string) List of split names
     */
    public function getSplitNames()
    {
        $cache = Di::getCache();
        $splitKeys = $cache->getKeys(self::getCacheKeySearchPattern());
        return array_map('self::getSplitNameFromCacheKey', $splitKeys);
    }

    /**
     * @return array(string) List of all split JSON strings
     */
    public function getAllSplits()
    {
        $splitNames = $this->getSplitNames();
        return $this->getSplits($splitNames);
    }

    private static function getCacheKeyForTrafficType($trafficType)
    {
        return str_replace('{trafficTypeName}', $trafficType, self::KEY_TRAFFIC_TYPE_CACHED);
    }

    /**
     * @param string $trafficType
     * @return bool
     */
    public function trafficTypeExists($trafficType)
    {
        $cache = Di::getCache();

        $count = $cache->getItem(self::getCacheKeyForTrafficType($trafficType))->get();
        // empty check for nullable value
        return (empty($count) || $count < 1) ? false : true;
    }
}
