<?php

namespace SplitIO\Component\Cache;

class SplitCache implements SplitCacheInterface
{
    public const KEY_TILL_CACHED_ITEM = 'SPLITIO.splits.till';

    public const KEY_SPLIT_CACHED_ITEM = 'SPLITIO.split.{splitName}';

    public const KEY_TRAFFIC_TYPE_CACHED = 'SPLITIO.trafficType.{trafficTypeName}';

    public const KEY_FLAG_SET_CACHED = 'SPLITIO.flagSet.{set}';

    /**
     * @var \SplitIO\Component\Cache\Pool
     */
    private $cache;

    /**
     * @param \SplitIO\Component\Cache\Pool $cache
     */
    public function __construct(Pool $cache)
    {
        $this->cache = $cache;
    }

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

    private static function getCacheKeyForFlagSet($flagSet)
    {
        return str_replace('{set}', $flagSet, self::KEY_FLAG_SET_CACHED);
    }

    private static function getSplitNameFromCacheKey($key)
    {
        $cacheKeyPrefix = self::getCacheKeyForSplit('');
        return str_replace($cacheKeyPrefix, '', $key);
    }

    /**
     * @return int
     */
    public function getChangeNumber()
    {
        $since = $this->cache->get(self::getCacheKeyForSinceParameter());
        // empty check for nullable value
        return (empty($since)) ? -1 : $since;
    }

    /**
     * @param string $splitName
     * @return string JSON representation
     */
    public function getSplit($splitName)
    {
        return $this->cache->get(self::getCacheKeyForSplit($splitName));
    }

    /**
     * @param array $splitNames
     * @return array
     */
    public function getSplits($splitNames)
    {
        $cacheItems = $this->cache->fetchMany(array_map([
            self::class, 'getCacheKeyForSplit'
        ], $splitNames));
        $toReturn = array();
        foreach ($cacheItems as $key => $value) {
            $toReturn[self::getSplitNameFromCacheKey($key)] = $value;
        }
        return $toReturn;
    }

    /**
     * @return array(string) List of split names
     */
    public function getSplitNames()
    {
        $splitKeys =  $this->cache->getKeys(self::getCacheKeySearchPattern());
        return array_map([self::class, 'getSplitNameFromCacheKey'], $splitKeys);
    }

    /**
     * @param array(string) List of flag set names
     * @return array(string) List of all feature flag names by flag sets
     */
    public function getNamesByFlagSets($flagSets)
    {
        $toReturn = array();
        if (empty($flagSets)) {
            return $toReturn;
        }
        foreach ($flagSets as $flagSet) {
            $toReturn[$flagSet] = $this->cache->sMembers(self::getCacheKeyForFlagSet($flagSet));
        }
        return $toReturn;
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
        $count = $this->cache->get(self::getCacheKeyForTrafficType($trafficType));
        // empty check for nullable value
        return (empty($count) || $count < 1) ? false : true;
    }
}
