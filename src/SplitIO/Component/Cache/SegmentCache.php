<?php
namespace SplitIO\Component\Cache;

class SegmentCache implements SegmentCacheInterface
{
    const KEY_SEGMENT_DATA = 'SPLITIO.segment.{segmentName}';

    const KEY_TILL_CACHED_ITEM = 'SPLITIO.segment.{segment_name}.till';

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

    private static function getCacheKeyForSegmentData($segmentName)
    {
        return str_replace('{segmentName}', $segmentName, self::KEY_SEGMENT_DATA);
    }

    private static function getCacheKeyForSinceParameter($segmentName)
    {
        return str_replace('{segment_name}', $segmentName, self::KEY_TILL_CACHED_ITEM);
    }

    /**
     * @param $segmentName
     * @param $key
     * @return mixed
     */
    public function isInSegment($segmentName, $key)
    {
        $segmentDataKey = self::getCacheKeyForSegmentData($segmentName);
        return $this->cache->isItemOnList($segmentDataKey, $key);
    }

    /**
     * @param $segmentName
     * @return mixed
     */
    public function getChangeNumber($segmentName)
    {
        $since = $this->cache->get(self::getCacheKeyForSinceParameter($segmentName));
        // empty check for nullable value
        return (empty($since)) ? -1 : $since;
    }
}
