<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;

class MetricsCache
{
    const KEY_LATENCY_BUCKET = "SPLITIO.latency.{metricName}.bucket.{bucketNumber}";

    /**
     * @param $bucketNumber
     * @return mixed
     */
    public static function getCacheKeyForLatencyButcket($metricName, $bucketNumber)
    {
        $key = str_replace('{bucketNumber}', $bucketNumber, self::KEY_LATENCY_BUCKET);
        return str_replace('{metricName}', $metricName, $key);
    }

    /**
     * @return mixed
     */
    public static function getCacheKeySearchLatencyPattern()
    {
        $key = str_replace('{bucketNumber}', '*', self::KEY_LATENCY_BUCKET);
        return str_replace('{metricName}', '*', $key);
    }

    /**
     * @param $key
     * @return string
     */
    public static function getMetricNameFromKey($key)
    {
        $shards = explode('.', $key);
        return implode('.', array_slice($shards, 2, -2));
    }

    /**
     * @param $key
     * @return int
     */
    public static function getBucketFromKey($key)
    {
        $shards = explode('.', $key);
        return (int) implode('.', array_slice($shards, -1));
    }

    /**
     * @param $bucketNumber
     * @return int
     */
    public static function addLatencyOnBucket($metricName, $bucketNumber)
    {
        return Di::getCache()->incrementKey(self::getCacheKeyForLatencyButcket($metricName, $bucketNumber));
    }

    /**
     * @param $key
     * @return mixed|string
     */
    public function getLatencyAndReset($key)
    {
        return Di::getCache()->getSet($key, 0);
    }
}
