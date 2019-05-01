<?php

namespace SplitIO\Test\Suite\Component;

use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Component\Cache\MetricsCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\TrafficTypeCache;

class KeyTest extends \PHPUnit_Framework_TestCase
{
    public function testMetricsGetCacheKeyForLatencyButcket()
    {
        $key = MetricsCache::getCacheKeyForLatencyBucket('abc', 'def');
        $this->assertEquals(
            $key,
            'SPLITIO/php-' . \SplitIO\version() . '/' . \SplitIO\getHostIpAddress() . '/latency.abc.bucket.def'
        );
    }

    public function testGetCacheKeySearchLatencyPattern()
    {
        $key = MetricsCache::getCacheKeySearchLatencyPattern();
        $this->assertEquals(
            $key,
            'SPLITIO/php-' . \SplitIO\version() . '/' . \SplitIO\getHostIpAddress() . '/latency.*.bucket.*'
        );
    }

    public function testGetMetricNameFromKey()
    {
        $metricName = MetricsCache::getMetricNameFromKey(
            'SPLITIO/php-' . \SplitIO\version() . '/' . \SplitIO\getHostIpAddress() . '/latency.abc.bucket.def'
        );

        $this->assertEquals($metricName, 'abc');
    }

    public function testGetBucketFromKey()
    {
        $metricName = MetricsCache::getBucketFromKey(
            'SPLITIO/php-' . \SplitIO\version() . '/' . \SplitIO\getHostIpAddress() . '/latency.abc.bucket.def'
        );

        $this->assertEquals($metricName, 'def');
    }

    public function testSplitGetCacheKeyForSinceParameter()
    {
        $key = SplitCache::getCacheKeyForSinceParameter();
        $this->assertEquals($key, SplitCache::KEY_TILL_CACHED_ITEM);
    }

    public function testSplitGetCacheKeySearchPattern()
    {
        $key = SplitCache::getCacheKeySearchPattern();
        $this->assertEquals($key, 'SPLITIO.split.*');
    }

    public function testSplitGetCacheKeyForSplit()
    {
        $key = SplitCache::getCacheKeyForSplit('abc');
        $this->assertEquals($key, 'SPLITIO.split.abc');
    }

    public function testSplitGetSplitNameFromCacheKey()
    {
        $splitName = SplitCache::getSplitNameFromCacheKey('SPLITIO.split.abc');
        $this->assertEquals($splitName, 'abc');
    }

    public function testSegmentGetCacheKeyForRegisterSegments()
    {
        $key = SegmentCache::getCacheKeyForRegisterSegments();
        $this->assertEquals($key, 'SPLITIO.segments.registered');
    }

    public function testSegmentGetCacheKeyForSegmentData()
    {
        $key = SegmentCache::getCacheKeyForSegmentData('abc');
        $this->assertEquals($key, 'SPLITIO.segment.abc');
    }

    public function testSegmentGetCacheKeyForSinceParameter()
    {
        $key = SegmentCache::getCacheKeyForSinceParameter('abc');
        $this->assertEquals($key, 'SPLITIO.segment.abc.till');
    }

    public function testTrafficTypeNameFromCache()
    {
        $trafficTypeName = SplitCache::getCacheKeyForTrafficType('abc');
        $this->assertEquals($trafficTypeName, 'SPLITIO.trafficType.abc');
    }
}
