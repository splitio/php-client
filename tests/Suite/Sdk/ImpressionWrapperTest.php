<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Sdk\ImpressionListenerWrapper;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Test\Suite\Sdk\Helpers\ListenerClient;
use SplitIO\Test\Suite\Sdk\Helpers\ListenerClientWithException;
use SplitIO\Test\Suite\Sdk\Helpers\ListenerClientWrong;

class ImpressionListenerTest extends \PHPUnit_Framework_TestCase
{
    private function addSplitsInCache()
    {
        $splitChanges = file_get_contents(__DIR__."/files/splitil.json");
        $this->assertJson($splitChanges);

        $splitCache = new SplitCache();

        $splitChanges = json_decode($splitChanges, true);
        $splits = $splitChanges['splits'];

        foreach ($splits as $split) {
            $splitName = $split['name'];
            $this->assertTrue($splitCache->addSplit($splitName, json_encode($split)));
        }
    }

    public function testSendDataToClient()
    {
        $impression = new Impression(
            $matchingKey = 'something',
            $feature = 'something',
            $treatment = TreatmentEnum::CONTROL,
            $label = null,
            $time = null,
            $changeNumber = -1,
            $bucketingKey = 'something'
        );

        $attributes = null;

        $impressionClient = new ListenerClient();
        $impressionWrapper = new ImpressionListenerWrapper($impressionClient);

        $impressionWrapper->sendDataToClient($impression, $attributes);

        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('impression', $impressionClient->dataLogged);
        $this->assertInstanceOf('SplitIO\Sdk\Impressions\Impression', $impressionClient->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient->dataLogged);
    }

    public function testClientThrowningExceptionInListener()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $impressionClient = new ListenerClientWithException();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'impressionListener' => $impressionClient,
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('valid', 'iltest'));
    }

    public function testClientWithNullIpAddress()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $impressionClient2 = new ListenerClient();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'impressionListener' => $impressionClient2,
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options),
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('valid', 'iltest'));

        $this->assertArrayHasKey('instance-id', $impressionClient2->dataLogged);
        $this->assertEquals($impressionClient2->dataLogged['instance-id'], 'unknown');
        $this->assertEquals($impressionClient2->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('impression', $impressionClient2->dataLogged);
        $this->assertEquals($impressionClient2->dataLogged['impression']->getTreatment(), 'on');
        $this->assertInstanceOf('SplitIO\Sdk\Impressions\Impression', $impressionClient2->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient2->dataLogged);
    }

    public function testClient()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $impressionClient = new ListenerClient();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'impressionListener' => $impressionClient,
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options),
            'ipAddress' => '1.2.3.4'
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('valid', 'iltest'));
        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['instance-id'], '1.2.3.4');
        $this->assertEquals($impressionClient->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('impression', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['impression']->getTreatment(), 'on');
        $this->assertInstanceOf('SplitIO\Sdk\Impressions\Impression', $impressionClient->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient->dataLogged);

        $this->assertEquals('off', $splitSdk->getTreatment('invalidKey', 'iltest'));

        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['instance-id'], '1.2.3.4');
        $this->assertEquals($impressionClient->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('impression', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['impression']->getTreatment(), 'off');
        $this->assertInstanceOf('SplitIO\Sdk\Impressions\Impression', $impressionClient->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient->dataLogged);
    }

    public function testClientWithEmptyIpAddress()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $impressionClient3 = new ListenerClient();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'impressionListener' => $impressionClient3,
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options),
            'ipAddress' => ""
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('valid', 'iltest'));

        $this->assertArrayHasKey('instance-id', $impressionClient3->dataLogged);
        $this->assertEquals($impressionClient3->dataLogged['instance-id'], 'unknown');
        $this->assertEquals($impressionClient3->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('impression', $impressionClient3->dataLogged);
        $this->assertEquals($impressionClient3->dataLogged['impression']->getTreatment(), 'on');
        $this->assertInstanceOf('SplitIO\Sdk\Impressions\Impression', $impressionClient3->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient3->dataLogged);
    }

    public function testClientWithEmptyStringIpAddress()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $impressionClient4 = new ListenerClient();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'impressionListener' => $impressionClient4,
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options),
            'ipAddress' => "     "
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('valid', 'iltest'));

        $this->assertArrayHasKey('instance-id', $impressionClient4->dataLogged);
        $this->assertEquals($impressionClient4->dataLogged['instance-id'], 'unknown');
        $this->assertEquals($impressionClient4->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('impression', $impressionClient4->dataLogged);
        $this->assertEquals($impressionClient4->dataLogged['impression']->getTreatment(), 'on');
        $this->assertInstanceOf('SplitIO\Sdk\Impressions\Impression', $impressionClient4->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient4->dataLogged);
    }

    public function testClientErasingServer()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $impressionClient4 = new ListenerClient();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'impressionListener' => $impressionClient4,
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        $_SERVER['SERVER_ADDR'] = "";

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('valid', 'iltest'));

        $this->assertArrayHasKey('instance-id', $impressionClient4->dataLogged);
        $this->assertEquals($impressionClient4->dataLogged['instance-id'], 'unknown');
        $this->assertEquals($impressionClient4->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('impression', $impressionClient4->dataLogged);
        $this->assertEquals($impressionClient4->dataLogged['impression']->getTreatment(), 'on');
        $this->assertInstanceOf('SplitIO\Sdk\Impressions\Impression', $impressionClient4->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient4->dataLogged);
    }

    public function testClientWithoutImpressionListener()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $impressionClient5 = new ListenerClient();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('valid', 'iltest'));
    }

    public function testClientWithNullImpressionListener()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $impressionClient6 = new ListenerClient();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'impressionListener' => null,
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('valid', 'iltest'));
    }
}
