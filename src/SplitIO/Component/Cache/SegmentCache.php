<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;

class SegmentCache implements SegmentCacheInterface
{
    const KEY_REGISTER_SEGMENTS = 'SPLITIO.segments.registered';

    const KEY_SEGMENT_DATA = 'SPLITIO.segment.{segmentName}';

    const KEY_TILL_CACHED_ITEM = 'SPLITIO.segment.{segment_name}.till';

    public static function getCacheKeyForRegisterSegments()
    {
        return self::KEY_REGISTER_SEGMENTS;
    }

    public static function getCacheKeyForSegmentData($segmentName)
    {
        return str_replace('{segmentName}', $segmentName, self::KEY_SEGMENT_DATA);
    }

    public static function getCacheKeyForSinceParameter($segmentName)
    {
        return str_replace('{segment_name}', $segmentName, self::KEY_TILL_CACHED_ITEM);
    }

    /**
     * @param $segmentName
     * @return boolean
     */
    public static function registerSegment($segmentName)
    {
        $cache = Di::getCache();

        return $cache->saveItemOnList(self::getCacheKeyForRegisterSegments(), $segmentName);
    }

    public static function getRegisteredSegments()
    {
        return Di::getCache()->getItemsOnList(self::getCacheKeyForRegisterSegments());
    }

    /**
     * @param $segmentName
     * @param $segmentKeys
     * @return mixed
     */
    public function addToSegment($segmentName, array $segmentKeys)
    {
        $cache = Di::getCache();
        $return = array();

        $segmentDataKey = self::getCacheKeyForSegmentData($segmentName);

        foreach ($segmentKeys as $key) {
            $return[$key] = $cache->saveItemOnList($segmentDataKey, $key);
        }

        return $return;
    }

    /**
     * @param $segmentName
     * @param array $segmentKeys
     * @return mixed
     */
    public function removeFromSegment($segmentName, array $segmentKeys)
    {
        $cache = Di::getCache();
        $return = array();

        $segmentDataKey = self::getCacheKeyForSegmentData($segmentName);

        foreach ($segmentKeys as $key) {
            $return[$key] = $cache->removeItemOnList($segmentDataKey, $key);
        }

        return $return;
    }

    /**
     * @param $segmentName
     * @param $key
     * @return mixed
     */
    public function isInSegment($segmentName, $key)
    {
        $segmentDataKey = self::getCacheKeyForSegmentData($segmentName);
        return Di::getCache()->isItemOnList($segmentDataKey, $key);
    }

    /**
     * @param $segmentName
     * @param $changeNumber
     * @return mixed
     */
    public function setChangeNumber($segmentName, $changeNumber)
    {
        $sinceKey = self::getCacheKeyForSinceParameter($segmentName);
        $since_cached_item = Di::getCache()->getItem($sinceKey);
        Di::getLogger()->info(">>> SINCE CACHE KEY: $sinceKey");
        $since_cached_item->set($changeNumber);

        return Di::getCache()->save($since_cached_item);
    }

    /**
     * @param $segmentName
     * @return mixed
     */
    public function getChangeNumber($segmentName)
    {
        $since = Di::getCache()->getItem(self::getCacheKeyForSinceParameter($segmentName))->get();
        return (empty($since)) ? -1 : $since;
    }
}
