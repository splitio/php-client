<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Component\Common\Di;
use SplitIO\Sdk\Key;

class GetTreatmentsValidationTest extends \PHPUnit_Framework_TestCase
{
    private function getFactoryClient()
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

        return $splitSdk;
    }

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

    public function testGetTreatmentsWithNullMatchingKeyObject()
    {
        $this->setExpectedException(\SplitIO\Exception\KeyException::class);

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatments(new Key(null, 'some_bucketing_key'), ['some_feature']));
    }

    public function testGetTreatmentsWithEmptyMatchingKeyObject()
    {
        $this->setExpectedException(\SplitIO\Exception\KeyException::class);

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatments(new Key('', 'some_bucketing_key'), ['some_feature']));
    }

    public function testGetTreatmentsWithWrongTypeMatchingKeyObject()
    {
        $this->setExpectedException(\SplitIO\Exception\KeyException::class);

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatments(new Key(true, 'some_bucketing_key'), ['some_feature']));
    }

    public function testGetTreatmentsWitNumberMatchingKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("Key: matchingKey '12345' is not of type string, converting.")
            ));

        $treatmentResult = $splitSdk->getTreatments(new Key(12345, 'some_bucketing_key'), ['some_feature']);

        $this->assertEquals(1, count(array_keys($treatmentResult)));

        $this->assertEquals('control', $treatmentResult['some_feature']);
    }

    public function testGetTreatmentsWithNullBucketingKeyObject()
    {
        $this->setExpectedException(\SplitIO\Exception\KeyException::class);

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatments(new Key('some_matching_key', null), ['some_feature']));
    }

    public function testGetTreatmentsWithEmptyBucketingKeyObject()
    {
        $this->setExpectedException(\SplitIO\Exception\KeyException::class);

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatments(new Key('some_matching_key', ''), ['some_feature']));
    }

    public function testGetTreatmentsWithWrongTypeBucketingKeyObject()
    {
        $this->setExpectedException(\SplitIO\Exception\KeyException::class);

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatments(new Key('', array()), ['some_feature']));
    }

    public function testGetTreatmentsWitNumberBucketingKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("Key: bucketingKey '12345' is not of type string, converting.")
            ));

        $treatmentResult = $splitSdk->getTreatments(new Key('some_matching_key', 12345), ['some_feature']);

        $this->assertEquals(1, count(array_keys($treatmentResult)));

        $this->assertEquals('control', $treatmentResult['some_feature']);
    }

    public function testGetTreatmentsWithNullKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("getTreatments: you passed a null key, key must be a non-empty string."));

        $this->assertEquals(null, $splitSdk->getTreatments(null, ['some_feature']));
    }

    public function testGetTreatmentsWithEmptyKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("getTreatments: you passed an empty key, key must be a non-empty string."));

        $this->assertEquals(null, $splitSdk->getTreatments('', ['some_feature']));
    }

    public function testGetTreatmenstWitNonFiniteKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('critical')
            ->with($this->equalTo("getTreatments: you passed an invalid key type, key must be a non-empty string."));

        $this->assertEquals(null, $splitSdk->getTreatments(log(0), ['some_feature']));
    }

    public function testGetTreatmentsWitNumberKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("getTreatments: key '123456' is not of type string, converting.")
            ));

        $treatmentResult = $splitSdk->getTreatments(123456, ['some_feature']);

        $this->assertEquals(1, count(array_keys($treatmentResult)));

        $this->assertEquals('control', $treatmentResult['some_feature']);
    }

    public function testGetTreatmentsWithNonFiniteMatchingKeyObject()
    {
        $this->setExpectedException(\SplitIO\Exception\KeyException::class);

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals(
            'control',
            $splitSdk->getTreatment(
                new Key(
                    log(0),
                    'some_bucketing_key'
                ),
                ['some_feature']
            )
        );
    }

    public function testGetTreatmentsWitKeyDifferentFromNumberObjectOrString()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("getTreatments: you passed an invalid key type, key must be a non-empty string."));

        $this->assertEquals(null, $splitSdk->getTreatments(true, ['some_feature']));
    }

    public function testGetTreatmentsWithNullFeatures()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatments: featureNames must be a non-empty array.'));

        $this->assertEquals(null, $splitSdk->getTreatments('some_key', null, null));
    }

    public function testGetTreatmentsWithFeaturesNotArray()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatments: featureNames must be a non-empty array.'));

        $this->assertEquals(null, $splitSdk->getTreatments('some_key', true, null));
    }

    public function testGetTreatmentsWithEmptyFeatures()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatments: featureNames must be a non-empty array.'));

        $this->assertEquals(null, $splitSdk->getTreatments('some_key', [], null));
    }

    public function testGetTreatmentsWithNullFeaturesNames()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        $logger->expects($this->at(0))
            ->method('critical')
            ->with($this->equalTo('getTreatments: you passed a null split name, split name must be a non-empty '
                . 'string.'));
        $logger->expects($this->at(1))
            ->method('critical')
            ->with($this->equalTo('getTreatments: you passed a null split name, split name must be a non-empty '
                . 'string.'));
        $logger->expects($this->at(2))
            ->method('critical')
            ->with($this->equalTo('getTreatments: featureNames must be a non-empty array.'));

        $this->assertEquals(null, $splitSdk->getTreatments('some_key', [null, null], null));
    }

    public function testGetTreatmentsWithOneWrongTypeOfFeaturesNames()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        $logger->expects($this->at(0))
            ->method('critical')
            ->with($this->equalTo('getTreatments: you passed an invalid split name, split name must be a non-empty '
            . 'string.'));
        $logger->expects($this->at(1))
            ->method('critical')
            ->with($this->equalTo('getTreatments: you passed an invalid split name, split name must be a non-empty '
            . 'string.'));
        $logger->expects($this->at(2))
            ->method('critical')
            ->with($this->equalTo('getTreatments: featureNames must be a non-empty array.'));
        
        $this->assertEquals(null, $splitSdk->getTreatments('some_key', [true, array()], null));
    }

    public function testGetTreatmenstWithFeatureNameWithWhitespaces()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo('getTreatments: split name "some_feature  " has extra whitespace, trimming.')
            ));

        $treatmentResult = $splitSdk->getTreatments("some_key", ['some_feature  ']);

        $this->assertEquals(1, count(array_keys($treatmentResult)));

        $this->assertEquals('control', $treatmentResult['some_feature']);
    }

    public function testGetTreatmenstWithFeatureNameWithWhitespaces2()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo('getTreatments: split name "   some_feature  " has extra whitespace, trimming.')
            ));

        $treatmentResult = $splitSdk->getTreatments("some_key", ['   some_feature  ']);

        $this->assertEquals(1, count(array_keys($treatmentResult)));

        $this->assertEquals('control', $treatmentResult['some_feature']);
    }
}
