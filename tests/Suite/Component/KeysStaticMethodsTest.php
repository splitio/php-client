<?php

namespace SplitIO\Test\Suite\Component;

use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\TrafficTypeCache;

class KeyTest extends \PHPUnit\Framework\TestCase
{

    private static function getStaticMethodAsPublic($className, $methodName)
    {
        $refMethod = new \ReflectionMethod($className, $methodName);
        $refMethod->setAccessible(true);
        return $refMethod;
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
