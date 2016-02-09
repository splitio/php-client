<?php
namespace SplitIO\Cache;

use SplitIO\Common\Di;

class SplitCache implements SplitCacheInterface
{
    const KEY_TILL_CACHED_ITEM = 'SPLITIO.splits.till';

    public static function getCacheKeyForSinceParameter()
    {
        return self::KEY_TILL_CACHED_ITEM;
    }

    public static function getCacheKeyForSplit($splitName)
    {
        return str_replace('{splitName}', $splitName, 'SPLITIO.split.{splitName}');
    }

    /**
     * @param string $splitName
     * @param string $split JSON representation
     * @return boolean
     */
    public function addSplit($splitName, $split)
    {
        $di = Di::getInstance();
        $sdkConfig = $di->getSplitSdkConfiguration();
        $cache = $di->getCache();

        $cacheItem = $cache->getItem(self::getCacheKeyForSplit($splitName));
        $cacheItem->set($split);
        $cacheItem->expiresAfter($sdkConfig->getCacheItemTtl());
        return $cache->save($cacheItem);
    }

    /**
     * @param string $splitName
     * @return boolean
     */
    public function removeSplit($splitName)
    {
        return Di::getInstance()->getCache()->deleteItem(self::getCacheKeyForSplit($splitName));
    }

    /**
     * @param long $changeNumber
     * @return boolean
     */
    public function setChangeNumber($changeNumber)
    {
        $since_cached_item = Di::getInstance()->getCache()->getItem(self::getCacheKeyForSinceParameter());

        $since_cached_item->set($changeNumber);

        //Refreshing the TTL of the item.
        $since_cached_item->expiresAfter(Di::getInstance()->getSplitSdkConfiguration()->getCacheItemTtl());

        return Di::getInstance()->getCache()->save($since_cached_item);
    }

    /**
     * @return long
     */
    public function getChangeNumber()
    {
        $since = Di::getInstance()->getCache()->getItem(self::getCacheKeyForSinceParameter())->get();
        return (empty($since)) ? -1 : $since;
    }

    /**
     * @param string $splitName
     * @return string JSON representation
     */
    public function getSplit($splitName)
    {
        $di = Di::getInstance();
        $cache = $di->getCache();

        $cacheItem = $cache->getItem(self::getCacheKeyForSplit($splitName));

        return $cacheItem->get();
    }
}