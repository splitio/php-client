<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Component\Common\Di;
use SplitIO\Sdk\Key;

class GetTreatmentsValidationTest extends \PHPUnit_Framework_TestCase
{
    private function getFactoryClient()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
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
        $this->setExpectedException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $key = new Key(null, 'some_bucketing_key');

        $this->assertEquals('control', $splitSdk->getTreatments($key, array('some_feature')));
    }

    public function testGetTreatmentsWithEmptyMatchingKeyObject()
    {
        $this->setExpectedException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $key = new Key('', 'some_bucketing_key');

        $this->assertEquals('control', $splitSdk->getTreatments($key, array('some_feature')));
    }

    public function testGetTreatmentsWithWrongTypeMatchingKeyObject()
    {
        $this->setExpectedException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $key = new Key(true, 'some_bucketing_key');

        $this->assertEquals('control', $splitSdk->getTreatments($key, array('some_feature')));
    }

    public function testGetTreatmentsWithNumberMatchingKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("Key: matchingKey '12345' is not of type string, converting."),
                $this->equalTo("getTreatments: you passed some_feature that does not exist in this environment, "
                    . "please double check what Splits exist in the web console.")
            ));

        $treatmentResult = $splitSdk->getTreatments(new Key(12345, 'some_bucketing_key'), array('some_feature'));

        $this->assertEquals(1, count(array_keys($treatmentResult)));

        $this->assertEquals('control', $treatmentResult['some_feature']);
    }

    public function testGetTreatmentsWithNullBucketingKeyObject()
    {
        $this->setExpectedException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $key = new Key('some_matching_key', null);

        $this->assertEquals('control', $splitSdk->getTreatments($key, array('some_feature')));
    }

    public function testGetTreatmentsWithEmptyBucketingKeyObject()
    {
        $this->setExpectedException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $key = new Key('some_matching_key', '');

        $this->assertEquals('control', $splitSdk->getTreatments($key, array('some_feature')));
    }

    public function testGetTreatmentsWithWrongTypeBucketingKeyObject()
    {
        $this->setExpectedException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatments(new Key('', array()), array('some_feature')));
    }

    public function testGetTreatmentsWitNumberBucketingKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("Key: bucketingKey '12345' is not of type string, converting."),
                $this->equalTo("getTreatments: you passed some_feature that does not exist in this environment, "
                    . "please double check what Splits exist in the web console.")
            ));

        $treatmentResult = $splitSdk->getTreatments(new Key('some_matching_key', 12345), array('some_feature'));

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

        $result = $splitSdk->getTreatments(null, array('some_feature'));
        $this->assertEquals(count($result), 1);
        $this->assertEquals('control', $result['some_feature']);
    }

    public function testGetTreatmentsWithEmptyKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("getTreatments: you passed an empty key, key must be a non-empty string."));

        $result = $splitSdk->getTreatments('', array('some_feature'));
        $this->assertEquals(count($result), 1);
        $this->assertEquals('control', $result['some_feature']);
    }

    public function testGetTreatmenstWitNonFiniteKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('critical')
            ->with($this->equalTo("getTreatments: you passed an invalid key type, key must be a non-empty string."));

        $result = $splitSdk->getTreatments(log(0), array('some_feature'));
        $this->assertEquals(count($result), 1);
        $this->assertEquals('control', $result['some_feature']);
    }

    public function testGetTreatmentsWitNumberKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("getTreatments: key '123456' is not of type string, converting."),
                $this->equalTo("getTreatments: you passed some_feature that does not exist in this environment, "
                    . "please double check what Splits exist in the web console.")
            ));

        $treatmentResult = $splitSdk->getTreatments(123456, array('some_feature'));

        $this->assertEquals(1, count(array_keys($treatmentResult)));

        $this->assertEquals('control', $treatmentResult['some_feature']);
    }

    public function testGetTreatmentsWithNonFiniteMatchingKeyObject()
    {
        $this->setExpectedException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals(
            'control',
            $splitSdk->getTreatment(
                new Key(
                    log(0),
                    'some_bucketing_key'
                ),
                array('some_feature')
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

        $result = $splitSdk->getTreatments(true, array('some_feature'));
        $this->assertEquals(count($result), 1);
        $this->assertEquals('control', $result['some_feature']);
    }

    public function testGetTreatmentsWithNullFeatures()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatments: featureNames must be a non-empty array.'));

        $this->assertEquals(array(), $splitSdk->getTreatments('some_key', null, null));
    }

    public function testGetTreatmentsWithFeaturesNotArray()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatments: featureNames must be a non-empty array.'));

        $this->assertEquals(array(), $splitSdk->getTreatments('some_key', true, null));
    }

    public function testGetTreatmentsWithEmptyFeatures()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatments: featureNames must be a non-empty array.'));

        $this->assertEquals(array(), $splitSdk->getTreatments('some_key', array(), null));
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

        $this->assertEquals(array(), $splitSdk->getTreatments('some_key', array(null, null), null));
    }

    public function testMultipleControlResultsGetTreatments()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("getTreatments: you passed an invalid key type, key must be a non-empty string."));

        $result = $splitSdk->getTreatments(true, array('some_feature', 'some_feature_3', 'some_feature',
            'some_feature_2'));
        $this->assertEquals(count($result), 3);
        $this->assertEquals('control', $result['some_feature']);
        $this->assertEquals('control', $result['some_feature_2']);
        $this->assertEquals('control', $result['some_feature_3']);
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

        $this->assertEquals(array(), $splitSdk->getTreatments('some_key', array(true, array()), null));
    }

    public function testGetTreatmenstWithFeatureNameWithWhitespaces()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo('getTreatments: split name "some_feature  " has extra whitespace, trimming.'),
                $this->equalTo("getTreatments: you passed some_feature that does not exist in this environment, "
                    . "please double check what Splits exist in the web console.")
            ));

        $treatmentResult = $splitSdk->getTreatments("some_key", array('some_feature  '));

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
                $this->equalTo('getTreatments: split name "   some_feature  " has extra whitespace, trimming.'),
                $this->equalTo("getTreatments: you passed some_feature that does not exist in this environment, "
                    . "please double check what Splits exist in the web console.")
            ));

        $treatmentResult = $splitSdk->getTreatments("some_key", array('   some_feature  '));

        $this->assertEquals(1, count(array_keys($treatmentResult)));

        $this->assertEquals('control', $treatmentResult['some_feature']);
    }

    public function testGetTreatmenstWithoutExistingFeatureName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo("getTreatments: you passed some_feature_non_existant that does"
                . " not exist in this environment, please double check what Splits exist in the web console."));

        $treatmentResult = $splitSdk->getTreatments("some_key", array('some_feature_non_existant'));

        $this->assertEquals(1, count(array_keys($treatmentResult)));

        $this->assertEquals('control', $treatmentResult['some_feature_non_existant']);
    }

    public function testGetTreatmenstConfigWithoutExistingFeatureName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo("getTreatmentsWithConfig: you passed some_feature_non_existant that does"
                . " not exist in this environment, please double check what Splits exist in the web console."));

        $treatmentResult = $splitSdk->getTreatmentsWithConfig("some_key", array('some_feature_non_existant'));

        $this->assertEquals(1, count(array_keys($treatmentResult)));

        $this->assertEquals('control', $treatmentResult['some_feature_non_existant']['treatment']);
    }
}
