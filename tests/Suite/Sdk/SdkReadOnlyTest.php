<?php

namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Cache\Storage\Adapter\PRedis;
use SplitIO\Component\Common\Di;
use SplitIO\Test\Suite\Redis\PRedisReadOnlyMock;
use SplitIO\TreatmentImpression;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Sdk\QueueMetadataMessage;

class SdkReadOnlyTest extends \PHPUnit\Framework\TestCase
{
    private function addSplitsInCache()
    {
        $splitChanges = file_get_contents(__DIR__."/files/splitReadOnly.json");
        $this->assertJson($splitChanges);
        $splitCache = new SplitCache();
        $splitChanges = json_decode($splitChanges, true);
        $splits = $splitChanges['splits'];
        foreach ($splits as $split) {
            $splitName = $split['name'];
            $this->assertTrue($splitCache->addSplit($splitName, json_encode($split)));
        }
    }

    public function testClient()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();
        $sdkConfig = array(
            'log' => array('adapter' => LOG_ADAPTER),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();

        //Instantiate PRedis Mocked Cache
        $predis = new PRedis(array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options));
        Di::set(Di::KEY_CACHE, new PRedisReadOnlyMock($predis));

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

        Di::set(Di::KEY_LOG, $logger);

        $this->assertEquals('on', $splitSdk->getTreatment('valid', 'mockedPRedis'));
        $this->assertEquals('off', $splitSdk->getTreatment('invalid', 'mockedPRedis'));
        $this->assertEquals('control', $splitSdk->getTreatment('valid', 'mockedPRedisInvalid'));
    }

    public function testException()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();
        $sdkConfig = array(
            'log' => array('adapter' => LOG_ADAPTER),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options),
            'static_cache' => array('class' => \VoidStaticCache::class)
        );

        //Initializing the SDK instance.
        \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);

        //Populating the cache.
        $this->addSplitsInCache();

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

        TreatmentImpression::log($impression, new QueueMetadataMessage());
    }
}
