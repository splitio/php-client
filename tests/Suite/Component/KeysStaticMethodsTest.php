<?php

namespace SplitIO\Test\Suite\Component;

use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Component\Cache\MetricsCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\TrafficTypeCache;

class KeyTest extends \PHPUnit_Framework_TestCase
{

    private static function getStaticMethodAsPublic($className, $methodName)
    {
        $refMethod = new \ReflectionMethod($className, $methodName);
        $refMethod->setAccessible(true);
        return $refMethod;
    }

    public function testMetricsGetCacheKeyForLatencyButcket()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\MetricsCache', 'getCacheKeyForLatencyBucket');
        $key = $method->invoke(null, 'abc', 'def');
        $this->assertEquals(
            $key,
            'SPLITIO/php-' . \SplitIO\version() . '/' . \SplitIO\getHostIpAddress() . '/latency.abc.bucket.def'
        );
    }

    public function testGetCacheKeySearchLatencyPattern()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\MetricsCache', 'getCacheKeySearchLatencyPattern');
        $key = $method->invoke(null);
        $this->assertEquals(
            $key,
            'SPLITIO/php-' . \SplitIO\version() . '/' . \SplitIO\getHostIpAddress() . '/latency.*.bucket.*'
        );
    }

    public function testGetMetricNameFromKey()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\MetricsCache', 'getMetricNameFromKey');
        $metricName = $method->invoke(
            null,
            'SPLITIO/php-' . \SplitIO\version() . '/' . \SplitIO\getHostIpAddress() . '/latency.abc.bucket.def'
        );

        $this->assertEquals($metricName, 'abc');
    }

    public function testGetBucketFromKey()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\MetricsCache', 'getBucketFromKey');
        $metricName = $method->invoke(
            null,
            'SPLITIO/php-' . \SplitIO\version() . '/' . \SplitIO\getHostIpAddress() . '/latency.abc.bucket.def'
        );

        $this->assertEquals($metricName, 'def');
    }

    public function testSplitGetCacheKeyForSinceParameter()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\SplitCache', 'getCacheKeyForSinceParameter');
        $key = $method->invoke(null);
        $this->assertEquals($key, SplitCache::KEY_TILL_CACHED_ITEM);
    }

    public function testSplitGetCacheKeySearchPattern()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\SplitCache', 'getCacheKeySearchPattern');
        $key = $method->invoke(null);
        $this->assertEquals($key, 'SPLITIO.split.*');
    }

    public function testSplitGetCacheKeyForSplit()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\SplitCache', 'getCacheKeyForSplit');
        $key = $method->invoke(null, 'abc');
        $this->assertEquals($key, 'SPLITIO.split.abc');
    }

    public function testSplitGetSplitNameFromCacheKey()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\SplitCache', 'getSplitNameFromCacheKey');
        $splitName = $method->invoke(null, 'SPLITIO.split.abc');
        $this->assertEquals($splitName, 'abc');
    }

    public function testSegmentGetCacheKeyForRegisterSegments()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\SegmentCache', 'getCacheKeyForRegisterSegments');
        $key = $method->invoke(null);
        $this->assertEquals($key, 'SPLITIO.segments.registered');
    }

    public function testSegmentGetCacheKeyForSegmentData()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\SegmentCache', 'getCacheKeyForSegmentData');
        $key = $method->invoke(null, 'abc');
        $this->assertEquals($key, 'SPLITIO.segment.abc');
    }

    public function testSegmentGetCacheKeyForSinceParameter()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\SegmentCache', 'getCacheKeyForSinceParameter');
        $key = $method->invoke(null, 'abc');
        $this->assertEquals($key, 'SPLITIO.segment.abc.till');
    }

    public function testTrafficTypeNameFromCache()
    {
        $method = self::getStaticMethodAsPublic('SplitIO\Component\Cache\SplitCache', 'getCacheKeyForTrafficType');
        $trafficTypeName = $method->invoke(null, 'abc');
        $this->assertEquals($trafficTypeName, 'SPLITIO.trafficType.abc');
    }
}
