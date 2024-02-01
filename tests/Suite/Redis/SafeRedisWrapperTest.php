<?php
namespace SplitIO\Test\Suite\Redis;

use SplitIO\Component\Cache\Storage\Adapter\SafeRedisWrapper;
use SplitIO\Component\Cache\Storage\Adapter\PRedis;

class SafeRedisWrapperTest extends \PHPUnit\Framework\TestCase
{
    public function testAllMethodsException()
    {
        // Set redis-library client mock
        $cachePoolMethods = array('get', 'mget', 'rpush', 'keys', 'sismember', 'expire', 'smembers');
        $predisMock = $this
            ->getMockBuilder('\Predis\Client')
            ->disableOriginalConstructor()
            ->addMethods($cachePoolMethods)
            ->onlyMethods(array('getOptions'))
            ->getMock();

        foreach ($cachePoolMethods as $method) {
            $predisMock->method($method)
                ->will($this->throwException(new \Exception()));
        }
        $predisMock->method('getOptions')
            ->will($this->throwException(new \Exception()));

        $predisMock->method('getOptions')
            ->willReturn(array());

        $predisAdapter = new PRedis(array(
            'parameters' => array(
                'scheme' => 'tcp',
                'host' => 'localhost',
                'port' => 6379,
            )
        ));
        $refObject = new \ReflectionObject($predisAdapter);
        $refProperty = $refObject->getProperty('client');
        $refProperty->setAccessible(true);
        $refProperty->setValue($predisAdapter, $predisMock);
        $safeRedisWrapper = new SafeRedisWrapper($predisAdapter);

        $this->assertEquals(false, $safeRedisWrapper->get("some"));
        $this->assertEquals(array(), $safeRedisWrapper->fetchMany(array("some")));
        $this->assertEquals(false, $safeRedisWrapper->isOnList("some", "another"));
        $this->assertEquals(array(), $safeRedisWrapper->getKeys("some"));
        $this->assertEquals(0, $safeRedisWrapper->rightPushQueue("some", "another"));
        $this->assertEquals(false, $safeRedisWrapper->expireKey("some", 12345));
        $this->assertEquals(array(), $safeRedisWrapper->sMembers("key"));
    }
}
