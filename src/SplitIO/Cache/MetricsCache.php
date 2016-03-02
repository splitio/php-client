<?php
namespace SplitIO\Cache;

use SplitIO\Split;

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
        return Split::cache()->incrementKey(self::getCacheKeyForLatencyButcket($metricName, $bucketNumber));
    }

    /**
     * @param $key
     */
    public function getLatencyAndReset($key)
    {
        return Split::cache()->getSet($key, 0);
    }
}
