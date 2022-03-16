<?php
namespace SplitIO\Test\Utils;

class Utils
{
    public static function addSplitsInCache($splitChanges)
    {
        $splitKey = "SPLITIO.split.";

        $predis = new \Predis\Client([
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
        ], ['prefix' => TEST_PREFIX]);
    
        if (is_null($splitChanges)) {
            return false;
        }
    
        $splitChanges = json_decode($splitChanges, true);
        $splits = $splitChanges['splits'];
    
        foreach ($splits as $split) {
            $splitName = $split['name'];
            $predis->set($splitKey . $splitName, json_encode($split));
        }
        return true;
    }

    public static function addSegmentsInCache($segmentChanges)
    {
        $segmentKey = "SPLITIO.segment.";

        $predis = new \Predis\Client([
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
        ], ['prefix' => TEST_PREFIX]);

        if (is_null($segmentChanges)) {
            return false;
        }

        $segmentData = json_decode($segmentChanges, true);
        $predis->sadd($segmentKey . $segmentData['name'], $segmentData['added']);

        return true;
    }

    public static function cleanCache()
    {
        $predis = new \Predis\Client([
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
        ]);

        $keys = $predis->keys(TEST_PREFIX . "*");
        foreach ($keys as $key) {
            $predis->del($key);
        }
    }
}
