<?php
namespace SplitIO\Test\Suite\Component;

use SplitIO\Component\Cache\TrafficTypeCache;
use SplitIO\Test\Suite\Redis\ReflectiveTools;
use SplitIO\Component\Common\Di;

class ImpressionsTest extends \PHPUnit_Framework_TestCase
{
    private function getMockedLogger()
    {
        //Initialize mock logger
        $logger = $this
            ->getMockBuilder('\SplitIO\Component\Log\Logger')
            ->disableOriginalConstructor()
            ->setMethods(array('warning', 'debug', 'error', 'info', 'critical', 'emergency',
                'alert', 'notice', 'write', 'log'))
            ->getMock();

        Di::set(Di::KEY_LOG, $logger);

        return $logger;
    }

    public function testImpressionsAreAdded()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        $logger = $this->getMockedLogger();

        $keyTrafficType = TrafficTypeCache::getCacheKeyForTrafficType('abc');

        $this->assertEquals($keyTrafficType, 'SPLITIO.trafficType.abc');

        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());
        $redisClient->del($keyTrafficType);

        $trafficTypeCache = new TrafficTypeCache();

        $this->assertEquals($trafficTypeCache->getTrafficType("abc"), 0);

        $logger->expects($this->once())
        ->method('warning')
        ->with($this->equalTo("track: Traffic Type 'abc' does not have any corresponding Splits "
            . "in this environment, make sure you’re tracking your events to a valid traffic "
            . "type defined in the Split console."));

        $this->assertEquals(true, $splitSdk->track('some_key', 'abc', 'some_event', 1));

        $redisClient->incr($keyTrafficType);

        $this->assertEquals(true, $splitSdk->track('some_key', 'abc', 'some_event', 1));

        $this->assertEquals($trafficTypeCache->getTrafficType("abc"), 1);

        $redisClient->del($keyTrafficType);
    }
}
