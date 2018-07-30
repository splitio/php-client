<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Sdk\ImpressionListenerWrapper;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;

class ListenerClient implements \SplitIO\Sdk\ImpressionListener {

    public $dataLogged;

    public function logImpression($data) {
        $this->dataLogged = $data;
    }
}

class ImpressionListenerTest extends \PHPUnit_Framework_TestCase
{
    private function addSplitsInCache()
    {
        $splitChanges = file_get_contents(__DIR__."/files/splitil.json");
        echo $splitChanges;
        $this->assertJson($splitChanges);

        $splitCache = new SplitCache();

        $splitChanges = json_decode($splitChanges, true);
        $splits = $splitChanges['splits'];

        foreach ($splits as $split) {
            $splitName = $split['name'];
            echo "NAME: $splitName\n";
            $this->assertTrue($splitCache->addSplit($splitName, json_encode($split)));
        }
    }

    public function testNoDefinedClassForWrapper()
    {
        $this->setExpectedException(\ArgumentCountError::class);

        $impressionWrapper = new ImpressionListenerWrapper();
    }

    public function testNoDefinedMethodForInterface()
    {
        $this->setExpectedException(\TypeError::class);

        $impressionWrapper = new ImpressionListenerWrapper($something);
    }

    public function testDefinedClassWhichImplementsImpressionListener()
    {
        $impressionClient = new ListenerClient();
        $impressionWrapper = new ImpressionListenerWrapper($impressionClient);
    }

    public function testNoArgumentsProvidedToSendDataToClient()
    {
        $this->setExpectedException(\ArgumentCountError::class);

        $impressionClient = new ListenerClient();
        $impressionWrapper = new ImpressionListenerWrapper($impressionClient);

        $impressionWrapper->sendDataToClient();
    }

    public function testNoImpressionInstanceProvidedToSendDataToClient()
    {
        $this->setExpectedException(\TypeError::class);
        
        $impressionClient = new ListenerClient();
        $impressionWrapper = new ImpressionListenerWrapper($impressionClient);

        $impressionWrapper->sendDataToClient($something);
    }

    public function testNoAttributesProvidedToSendDataToClient()
    {
        $this->setExpectedException(\ArgumentCountError::class);

        $impression = new Impression(
            $matchingKey = 'something',
            $feature = 'something',
            $treatment = TreatmentEnum::CONTROL,
            $label = null,
            $time = null,
            $changeNumber = -1,
            $bucketingKey = 'something'
        );
        
        $impressionClient = new ListenerClient();
        $impressionWrapper = new ImpressionListenerWrapper($impressionClient);

        $impressionWrapper->sendDataToClient($impression);
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
        
        $impressionClient = new ListenerClient();
        $impressionWrapper = new ImpressionListenerWrapper($impressionClient);

        $impressionWrapper->sendDataToClient($impression, $attributes);

        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertArrayHasKey('impression', $impressionClient->dataLogged);
        $this->assertInstanceOf(Impression::class, $impressionClient->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient->dataLogged);
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
        $this->assertEquals('on', $splitSdk->getTreatment('melograno', 'iltest'));

        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertArrayHasKey('impression', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['impression']->getTreatment(), 'on');
        $this->assertInstanceOf(Impression::class, $impressionClient->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient->dataLogged);

        $this->assertEquals('off', $splitSdk->getTreatment('invalidKey', 'iltest'));

        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertArrayHasKey('impression', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['impression']->getTreatment(), 'off');
        $this->assertInstanceOf(Impression::class, $impressionClient->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient->dataLogged);

        $this->assertEquals('control', $splitSdk->getTreatment('invalidKey', 'iltestNotExistant'));

        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['sdk-language-version'], 'php-'.\SplitIO\version());
        $this->assertArrayHasKey('instance-id', $impressionClient->dataLogged);
        $this->assertArrayHasKey('impression', $impressionClient->dataLogged);
        $this->assertEquals($impressionClient->dataLogged['impression']->getTreatment(), 'control');
        $this->assertInstanceOf(Impression::class, $impressionClient->dataLogged['impression']);
        $this->assertArrayHasKey('attributes', $impressionClient->dataLogged);
    }
}
