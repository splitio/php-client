<?php
namespace SplitIO\Component\Cache;

use SplitIO\Split;
use SplitIO\Component\Common\Di;
use SplitIO\Component\Cache\KeyFactory;

class MetricsCache
{
    const KEY_LATENCY_BUCKET =
        "SPLITIO/{sdk-language-version}/{instance-id}/latency.{metricName}.bucket.{bucketNumber}";

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

    /** TODO: metrics with a dot as part of name will fail.
     * @param $key
     * @return string
     */
    public static function getMetricNameFromKey($key)
    {
        $explodeKey = explode('/', $key);
        $lastShard = $explodeKey[3];
        $explodeLastShard = explode('.', $lastShard);
        return $explodeLastShard[1];
    }

    /** TODO: VERIFY AND REFACTOR
     * @param $key
     * @return int
     */
    public static function getBucketFromKey($key)
    {
        $explodeKey = explode('/', $key);
        $lastShard = $explodeKey[3];
        $explodeLastShard = explode('.', $lastShard);
        return $explodeLastShard[3];
    }

    /**
     * @param $bucketNumber
     * @return int
     */
    public static function addLatencyOnBucket($metricName, $bucketNumber)
    {
        try {
            return Di::getCache()->incrementKey(self::getCacheKeyForLatencyBucket($metricName, $bucketNumber));
        } catch (\Exception $e) {
            Di::getLogger()->warning('Unable to write metrics back to redis.');
            Di::getLogger()->warning($e->getMessage());
        }
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
