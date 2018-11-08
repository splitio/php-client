<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Component\Common\Di;
use SplitIO\TreatmentImpression;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Test\Suite\Redis\ReflectiveTools;
use SplitIO\Component\Cache\ImpressionCache;

class ImpressionsTest extends \PHPUnit_Framework_TestCase
{
    public function testImpressionsAreAdded()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);

        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());

        $redisClient->del("SPLITIO.impressions");

        TreatmentImpression::log(new Impression(
            'someMatchingKey',
            'someFeature',
            'on',
            'label1',
            123456,
            321654,
            'someBucketingKey'
        ));

        // Assert that the TTL is within a 10-second range (between it was set and retrieved).
        $ttl = $redisClient->ttl('SPLITIO.impressions');
        $this->assertLessThanOrEqual(ImpressionCache::IMPRESSION_KEY_DEFAULT_TTL, $ttl);
        $this->assertGreaterThanOrEqual(ImpressionCache::IMPRESSION_KEY_DEFAULT_TTL - 10, $ttl);

        $imp = $redisClient->rpop('SPLITIO.impressions');
        $decoded = json_decode($imp, true);

        $this->assertEquals($decoded['m']['s'], 'php-'.\Splitio\version());
        $this->assertEquals($decoded['m']['i'], 'unknown');
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
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);

        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());
        $redisClient->del("SPLITIO.impressions");

        TreatmentImpression::log(new Impression(
            'someMatchingKey',
            'someFeature',
            'on',
            'label1',
            123456,
            321654,
            'someBucketingKey'
        ));

        sleep(3);

        TreatmentImpression::log(new Impression(
            'someMatchingKey',
            'someFeature',
            'on',
            'label1',
            123456,
            321654,
            'someBucketingKey'
        ));

        $ttl = $redisClient->ttl(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        // $ttl should be lower than or equalt the default impressions TTL minus 3 seconds,
        // since it should have not been resetted with the last imrpession logged.
        $this->assertLessThanOrEqual(ImpressionCache::IMPRESSION_KEY_DEFAULT_TTL - 3, $ttl);
    }
}
