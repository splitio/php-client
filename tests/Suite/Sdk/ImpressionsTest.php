<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Component\Common\Di;
use SplitIO\TreatmentImpression;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Test\Suite\Redis\ReflectiveTools;
use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Sdk\QueueMetadataMessage;

use SplitIO\Test\Utils;

class ImpressionsTest extends \PHPUnit\Framework\TestCase
{
    public function testImpressionsAreAdded()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);

        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());

        $redisClient->del(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $queueMetadata = new QueueMetadataMessage();

        TreatmentImpression::log(new Impression(
            'someMatchingKey',
            'someFeature',
            'on',
            'label1',
            123456,
            321654,
            'someBucketingKey'
        ), $queueMetadata);

        // Assert that the TTL is within a 10-second range (between it was set and retrieved).
        $ttl = $redisClient->ttl(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertLessThanOrEqual(ImpressionCache::IMPRESSION_KEY_DEFAULT_TTL, $ttl);
        $this->assertGreaterThanOrEqual(ImpressionCache::IMPRESSION_KEY_DEFAULT_TTL - 10, $ttl);

        $imp = $redisClient->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $decoded = json_decode($imp, true);

        $this->assertEquals($decoded['m']['s'], 'php-'.\Splitio\version());
        $this->assertEquals($decoded['m']['i'], 'unknown');
        $this->assertEquals($decoded['m']['n'], 'unknown');
        $this->assertEquals($decoded['i']['k'], 'someMatchingKey');
        $this->assertEquals($decoded['i']['b'], 'someBucketingKey');
        $this->assertEquals($decoded['i']['f'], 'someFeature');
        $this->assertEquals($decoded['i']['t'], 'on');
        $this->assertEquals($decoded['i']['r'], 'label1');
        $this->assertEquals($decoded['i']['m'], 123456);
        $this->assertEquals($decoded['i']['c'], 321654);
    }

    public function testExpirationOnlyOccursOnce()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options),
            'IPAddressEnabled' => false
        );

        //Initializing the SDK instance.
        \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);

        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());
        $redisClient->del(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $queueMetadata = new QueueMetadataMessage(false);

        TreatmentImpression::log(new Impression(
            'someMatchingKey',
            'someFeature',
            'on',
            'label1',
            123456,
            321654,
            'someBucketingKey'
        ), $queueMetadata);

        sleep(3);

        TreatmentImpression::log(new Impression(
            'someMatchingKey',
            'someFeature',
            'on',
            'label1',
            123456,
            321654,
            'someBucketingKey'
        ), $queueMetadata);

        $ttl = $redisClient->ttl(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        // $ttl should be lower than or equalt the default impressions TTL minus 3 seconds,
        // since it should have not been resetted with the last imrpession logged.
        $this->assertLessThanOrEqual(ImpressionCache::IMPRESSION_KEY_DEFAULT_TTL - 3, $ttl);

        $imp = $redisClient->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $decoded = json_decode($imp, true);

        $this->assertEquals($decoded['m']['s'], 'php-'.\Splitio\version());
        $this->assertEquals($decoded['m']['i'], 'NA');
        $this->assertEquals($decoded['m']['n'], 'NA');
        $this->assertEquals($decoded['i']['k'], 'someMatchingKey');
        $this->assertEquals($decoded['i']['b'], 'someBucketingKey');
        $this->assertEquals($decoded['i']['f'], 'someFeature');
        $this->assertEquals($decoded['i']['t'], 'on');
        $this->assertEquals($decoded['i']['r'], 'label1');
        $this->assertEquals($decoded['i']['m'], 123456);
        $this->assertEquals($decoded['i']['c'], 321654);
    }

    public static function tearDownAfterClass(): void
    {
        Utils\Utils::cleanCache();
    }
}
