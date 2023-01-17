<?php

namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Component\Cache\Storage\Adapter\PRedis;
use SplitIO\Component\Common\Context;
use SplitIO\Test\Suite\Redis\PRedisReadOnlyMock;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Sdk\QueueMetadataMessage;
use SplitIO\Test\Suite\Redis\ReflectiveTools;

use SplitIO\Test\Utils;

class SdkReadOnlyTest extends \PHPUnit\Framework\TestCase
{
    public function testClient()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);
        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        Utils\Utils::addSplitsInCache(file_get_contents(__DIR__."/files/splitReadOnly.json"));

        //Instantiate PRedis Mocked Cache
        $predis = new PRedis(array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options));

        //Initialize mock logger
        $logger = $this
            ->getMockBuilder('\SplitIO\Component\Log\Logger')
            ->disableOriginalConstructor()
            ->setMethods(array('warning', 'debug', 'error', 'info', 'critical', 'emergency',
                'alert', 'notice', 'write', 'log'))
            ->getMock();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo('READONLY mode mocked.'),
                $this->equalTo('Unable to write impression back to redis.'),
                $this->equalTo('Unable to write metrics back to redis.'),
                $this->equalTo('The SPLIT definition for \'mockedPRedisInvalid\' has not been found')
            ));

        Context::setLogger($logger);

        $this->assertEquals('on', $splitSdk->getTreatment('valid', 'mockedPRedis'));
        $this->assertEquals('off', $splitSdk->getTreatment('invalid', 'mockedPRedis'));
        $this->assertEquals('control', $splitSdk->getTreatment('valid', 'mockedPRedisInvalid'));
    }

    /*
    @TODO FIND A WAY TO IMPLEMENT
    public function testException()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);
        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $factory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);

        //Populating the cache.
        Utils\Utils::addSplitsInCache(file_get_contents(__DIR__."/files/splitReadOnly.json"));

        //Instantiate PRedis Mocked Cache
        $predis = new PRedis(array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options));
        Di::set(Di::KEY_CACHE, new PRedisReadOnlyMock($predis));

        //Initializing mocked logger
        $logger = $this
            ->getMockBuilder('\SplitIO\Component\Log\Logger')
            ->disableOriginalConstructor()
            ->setMethods(array('warning', 'debug'))
            ->getMock();

        // Discard (ignore) first logging statement
        $logger->expects($this->exactly(2))
            ->method('debug');

        $logger->expects($this->exactly(2))
            ->method('warning')
            ->willReturnOnConsecutiveCalls(
                'Unable to write impression back to redis.',
                'READONLY mode mocked.'
            );

        $logger->expects($this->exactly(2))
            ->method('warning');

        Di::set(Di::KEY_LOG, $logger);

        $impression = new Impression(
            'something',
            'something',
            TreatmentEnum::CONTROL,
            null,
            null,
            -1,
            'something'
        );

        $cachePool = ReflectiveTools::cacheFromFactory($factory);
        $logger = $this
            ->getMockBuilder('\SplitIO\Component\Log\Logger')
            ->disableOriginalConstructor()
            ->setMethods(array('warning', 'debug'))
            ->getMock();
        $impressionCache = new ImpressionCache($cachePool);
        $impressionCache->logImpressions(array($impression), new QueueMetadataMessage());
    }

    */

    public static function tearDownAfterClass(): void
    {
        Utils\Utils::cleanCache();
    }
}
