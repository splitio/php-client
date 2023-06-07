<?php

namespace SplitIO\Test\Suite\Redis;

use ReflectionClass;
use SplitIO\Component\Common\Context;

class ReflectiveTools
{
    public static function cacheFromFactory(\SplitIO\Sdk\Factory\SplitFactory $factory)
    {
        $reflectionFactory = new ReflectionClass('\SplitIO\Sdk\Factory\SplitFactory');
        $reflectionCache = $reflectionFactory->getProperty('cache');
        $reflectionCache->setAccessible(true);
        return $reflectionCache->getValue($factory);
    }

    public static function clientFromFactory(\SplitIO\Sdk\Factory\SplitFactory $factory)
    {
        $reflectionFactory = new ReflectionClass('\SplitIO\Sdk\Factory\SplitFactory');
        $reflectionCache = $reflectionFactory->getProperty('cache');
        $reflectionCache->setAccessible(true);
        $cachePool = $reflectionCache->getValue($factory);

        $reflectionPool = new ReflectionClass('\SplitIO\Component\Cache\Pool');
        $reflectionAdapter = $reflectionPool->getProperty('adapter');
        $reflectionAdapter->setAccessible(true);
        $adapter = $reflectionAdapter->getValue($cachePool);

        $reflectionSafeRedis = new ReflectionClass('SplitIO\Component\Cache\Storage\Adapter\SafeRedisWrapper');
        $reflectionCacheAdapter= $reflectionSafeRedis->getProperty('cacheAdapter');
        $reflectionCacheAdapter->setAccessible(true);
        $adapter = $reflectionCacheAdapter->getValue($adapter);

        $reflectionPRedis = new ReflectionClass('SplitIO\Component\Cache\Storage\Adapter\PRedis');
        $reflectionClient= $reflectionPRedis->getProperty('client');
        $reflectionClient->setAccessible(true);
        return $reflectionClient->getValue($adapter);
    }

    public static function clientFromCachePool(\SplitIO\Component\Cache\Pool $cachePool)
    {
        $reflectionPool = new ReflectionClass('\SplitIO\Component\Cache\Pool');
        $reflectionAdapter = $reflectionPool->getProperty('adapter');
        $reflectionAdapter->setAccessible(true);
        $adapter = $reflectionAdapter->getValue($cachePool);

        $reflectionSafeRedis = new ReflectionClass('SplitIO\Component\Cache\Storage\Adapter\SafeRedisWrapper');
        $reflectionCacheAdapter= $reflectionSafeRedis->getProperty('cacheAdapter');
        $reflectionCacheAdapter->setAccessible(true);
        $adapter = $reflectionCacheAdapter->getValue($adapter);

        $reflectionPRedis = new ReflectionClass('SplitIO\Component\Cache\Storage\Adapter\PRedis');
        $reflectionClient= $reflectionPRedis->getProperty('client');
        $reflectionClient->setAccessible(true);
        return $reflectionClient->getValue($adapter);
    }

    public static function overrideLogger($logger)
    {
        $di = Context::getInstance();
        $reflection = new ReflectionClass('SplitIO\Component\Common\Context');
        $property = $reflection->getProperty('logger');
        $property->setAccessible(true);
        $property->setValue($di, $logger);
    }

    public static function resetIPAddress()
    {
        $di = Context::getInstance();
        $reflection = new ReflectionClass('SplitIO\Component\Common\Context');
        $property = $reflection->getProperty('ipAddress');
        $property->setAccessible(true);
        $property->setValue($di, "");
    }

    public static function overrideTracker()
    {
        $di = Context::getInstance();
        $reflection = new ReflectionClass('SplitIO\Component\Common\Context');
        $property = $reflection->getProperty('factoryTracker');
        $property->setAccessible(true);
        $property->setValue($di, array());
    }

    public static function resetContext()
    {
        $context = Context::getInstance();
        $reflection = new ReflectionClass($context);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        $instance->setAccessible(false);
    }
}
