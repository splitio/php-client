<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;

class TrafficTypeCache
{
    const KEY_TRAFFIC_TYPE_CACHED = 'SPLITIO.trafficType.{trafficTypeName}';

    public static function getCacheKeyForTrafficType($trafficType)
    {
        return str_replace('{trafficTypeName}', $trafficType, self::KEY_TRAFFIC_TYPE_CACHED);
    }

    /**
     * @param string $trafficType
     * @return string JSON representation
     */
    public function getTrafficType($trafficType)
    {
        $cache = Di::getCache();

        $count = $cache->getItem(self::getCacheKeyForTrafficType($trafficType))->get();
        return (empty($count)) ? 0 : $count;
    }
}
