<?php
namespace SplitIO\Test\Utils;

class Utils
{
    public static function addSplitsInCache($splitChanges)
    {
        $splitKey = "SPLITIO.split.";
        $tillKey = "SPLITIO.splits.till";
        $flagSetKey = "SPLITIO.flagSet.";

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

            $sets = $split['sets'];
            foreach ($sets as $set) {
                $predis->sadd($flagSetKey . $set, $splitName);
            }
        }
        $till = -1;
        if (isset($splitChanges['till'])) {
            $till = $splitChanges['till'];
        }
        $predis->set($tillKey, $till);
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
        $predis->set($segmentKey . $segmentData['name'] . ".till", $segmentData['till']);

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
