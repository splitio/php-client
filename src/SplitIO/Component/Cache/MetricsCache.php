<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;
use SplitIO\Component\Cache\KeyFactory;

class MetricsCache
{
    const KEY_LATENCY_BUCKET = "SPLITIO/{sdk-language-version}/{instance-id}/latency.{metricName}.bucket.{bucketNumber}";

    /**
     * @param $bucketNumber
     * @return mixed
     */
    public static function getCacheKeyForLatencyBucket($metricName, $bucketNumber)
    {
        return KeyFactory::make(self::KEY_LATENCY_BUCKET, array(
            '{bucketNumber}' => $bucketNumber,
            '{metricName}' => $metricName
        ));
    }

    /**
     * @return mixed
     */
    public static function getCacheKeySearchLatencyPattern()
    {
        return KeyFactory::make(self::KEY_LATENCY_BUCKET, array(
            '{bucketNumber}' => '*',
            '{metricName}' => '*'
        ));
    }

    /** TODO: VERIFY AND REFACTOR
     * @param $key
     * @return string
     */
    public static function getMetricNameFromKey($key)
    {
        $lastShard = explode('/', $key)[3];
        return explode('.', $lastShard)[1];
    }

    /** TODO: VERIFY AND REFACTOR
     * @param $key
     * @return int
     */
    public static function getBucketFromKey($key)
    {
        $lastShard = explode('/', $key)[3];
        return explode('.', $lastShard)[3];
    }

    /**
     * @param $bucketNumber
     * @return int
     */
    public static function addLatencyOnBucket($metricName, $bucketNumber)
    {
        return Di::getCache()->incrementKey(self::getCacheKeyForLatencyBucket($metricName, $bucketNumber));
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
