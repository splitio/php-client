<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;

class SplitCache implements SplitCacheInterface
{
    const KEY_TILL_CACHED_ITEM = 'SPLITIO.splits.till';

    const KEY_SPLIT_CACHED_ITEM = 'SPLITIO.split.{splitName}';

    const KEY_TRAFFIC_TYPE_CACHED = 'SPLITIO.trafficType.{trafficTypeName}';

    public static function getCacheKeyForSinceParameter()
    {
        return self::KEY_TILL_CACHED_ITEM;
    }

    public static function getCacheKeySearchPattern()
    {
        return self::getCacheKeyForSplit('*');
    }

    public static function getCacheKeyForSplit($splitName)
    {
        return str_replace('{splitName}', $splitName, self::KEY_SPLIT_CACHED_ITEM);
    }

    public static function getSplitNameFromCacheKey($key)
    {
        $cacheKeyPrefix = self::getCacheKeyForSplit('');
        return str_replace($cacheKeyPrefix, '', $key);
    }

    /**
     * @param string $splitName
     * @param string $split JSON representation
     * @return boolean
     */
    public function addSplit($splitName, $split)
    {
        $cache = Di::getCache();

        $cacheItem = $cache->getItem(self::getCacheKeyForSplit($splitName));
        $cacheItem->set($split);
        return $cache->save($cacheItem);
    }

    /**
     * @param string $splitName
     * @return boolean
     */
    public function removeSplit($splitName)
    {
        return Di::getCache()->deleteItem(self::getCacheKeyForSplit($splitName));
    }

    /**
     * @param long $changeNumber
     * @return boolean
     */
    public function setChangeNumber($changeNumber)
    {
        $since_cached_item = Di::getCache()->getItem(self::getCacheKeyForSinceParameter());
        $since_cached_item->set($changeNumber);

        return Di::getCache()->save($since_cached_item);
    }

    /**
     * @return long
     */
    public function getChangeNumber()
    {
        $since = Di::getCache()->getItem(self::getCacheKeyForSinceParameter())->get();
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

    public static function getCacheKeyForTrafficType($trafficType)
    {
        return str_replace('{trafficTypeName}', $trafficType, self::KEY_TRAFFIC_TYPE_CACHED);
    }

    /**
     * @param string $trafficType
     * @return string JSON representation
     */
    public function trafficTypeExists($trafficType)
    {
        $cache = Di::getCache();

        $count = $cache->getItem(self::getCacheKeyForTrafficType($trafficType))->get();
        return (empty($count) || $count < 1) ? false : true;
    }
}
