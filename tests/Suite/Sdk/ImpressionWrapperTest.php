<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Sdk\ImpressionListenerWrapper;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Test\Suite\Sdk\Helpers\ListenerClient;
use SplitIO\Test\Suite\Sdk\Helpers\ListenerClientWithException;

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
        $this->assertInstanceOf(Impression::class, $impressionClient->dataLogged['impression']);
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

    public function testClient()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $impressionClient = new ListenerClient();

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

        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('impression', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['impression']->getTreatment(), 'on');
        $this->assertInstanceOf(Impression::class, $impressionClient->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient->dataLogged);

        $this->assertEquals('off', $splitSdk->getTreatment('invalidKey', 'iltest'));

        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('impression', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['impression']->getTreatment(), 'off');
        $this->assertInstanceOf(Impression::class, $impressionClient->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient->dataLogged);

        $this->assertEquals('control', $splitSdk->getTreatment('invalidKey', 'iltestNotExistant'));

        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('impression', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['impression']->getTreatment(), 'control');
        $this->assertInstanceOf(Impression::class, $impressionClient->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient->dataLogged);
    }
}
