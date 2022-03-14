<?php

namespace SplitIO\Test\Suite\Redis;

use ReflectionClass;

class ReflectiveTools
{
    public static function clientFromCachePool(\SplitIO\Component\Cache\Pool $cachePool)
    {
        $reflectionPool = new ReflectionClass('\SplitIO\Component\Cache\Pool');
        $reflectionAdapter = $reflectionPool->getProperty('adapter');
        $reflectionAdapter->setAccessible(true);
        $adapter = $reflectionAdapter->getValue($cachePool);

        $reflectionPRedisW = new ReflectionClass('SplitIO\Component\Cache\Storage\Adapter\PRedisWrapperException');
        $reflectionCacheAdapter= $reflectionPRedisW->getProperty('cacheAdapter');
        $reflectionCacheAdapter->setAccessible(true);
        $adapter = $reflectionCacheAdapter->getValue($adapter);

        $reflectionPRedis = new ReflectionClass('SplitIO\Component\Cache\Storage\Adapter\PRedis');
        $reflectionClient= $reflectionPRedis->getProperty('client');
        $reflectionClient->setAccessible(true);
        return $reflectionClient->getValue($adapter);
    }
}
