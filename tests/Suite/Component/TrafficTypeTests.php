<?php
namespace SplitIO\Test\Suite\Component;

use SplitIO\Component\Cache\SplitCache;
use SplitIO\Test\Suite\Redis\ReflectiveTools;
use SplitIO\Component\Common\Di;

class TrafficTypeTest extends \PHPUnit\Framework\TestCase
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

    public function testTrafficTypeWarning()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('adapter' => LOG_ADAPTER),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        $logger = $this->getMockedLogger();

        $keyTrafficType = SplitCache::getCacheKeyForTrafficType('abc');

        $this->assertEquals($keyTrafficType, 'SPLITIO.trafficType.abc');

        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());
        $redisClient->del($keyTrafficType);

        $splitCache = new SplitCache();

        $this->assertEquals($splitCache->trafficTypeExists("abc"), false);

        $logger->expects($this->once())
        ->method('warning')
        ->with($this->equalTo("track: Traffic Type 'abc' does not have any corresponding Splits "
            . "in this environment, make sure you’re tracking your events to a valid traffic "
            . "type defined in the Split console."));

        $this->assertEquals(true, $splitSdk->track('some_key', 'abc', 'some_event', 1));

        $redisClient->incr($keyTrafficType);

        $this->assertEquals(true, $splitSdk->track('some_key', 'abc', 'some_event', 1));

        $this->assertEquals($splitCache->trafficTypeExists("abc"), true);

        $redisClient->del($keyTrafficType);
    }
}
