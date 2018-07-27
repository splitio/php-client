<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Sdk\ImpressionListenerWrapper;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;

global $assertReadImpression;

class ListenerClientTest implements \SplitIO\Sdk\ImpressionListener {
    public function readImpression($data) {
        $assertReadImpression = "test";
    }
}

class ImpressionListenerTest extends \PHPUnit_Framework_TestCase
{
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
        $impressionClient = new ListenerClientTest();
        $impressionWrapper = new ImpressionListenerWrapper($impressionClient);
    }

    public function testNoArgumentsProvidedToSendDataToClient()
    {
        $this->setExpectedException(\ArgumentCountError::class);

        $impressionClient = new ListenerClientTest();
        $impressionWrapper = new ImpressionListenerWrapper($impressionClient);

        $impressionWrapper->sendDataToClient();
    }

    public function testNoImpressionInstanceProvidedToSendDataToClient()
    {
        $this->setExpectedException(\TypeError::class);
        
        $impressionClient = new ListenerClientTest();
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
        
        $impressionClient = new ListenerClientTest();
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
        
        $impressionClient = new ListenerClientTest();
        $impressionWrapper = new ImpressionListenerWrapper($impressionClient);

        $impressionWrapper->sendDataToClient($impression, $attributes);

        // echo "And Here??????".$assertReadImpression['sdk-language-version']."\n";
        echo "And Here??????".$assertReadImpression."\n";
    }

    public function testClient()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $impressionClient = new ListenerClientTest();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'impressionListener' => $impressionClient,
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'sample_feature'));
        $this->assertEquals('off', $splitSdk->getTreatment('invalidKey', 'sample_feature'));
        $this->assertEquals('control', $splitSdk->getTreatment('invalidKey', 'invalid_feature'));

        // echo "And Here??????".$assertReadImpression['sdk-language-version']."\n";
        echo "And Here??????".$assertReadImpression."\n";
    }
}
