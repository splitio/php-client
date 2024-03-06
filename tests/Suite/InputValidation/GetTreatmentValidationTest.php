<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Test\Suite\Redis\ReflectiveTools;
use SplitIO\Sdk\Key;

use SplitIO\Test\Utils;

class GetTreatmentValidationTest extends \PHPUnit\Framework\TestCase
{
    private function getFactoryClient()
    {
        ReflectiveTools::overrideTracker();
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

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
            ->onlyMethods(array('warning', 'debug', 'error', 'info', 'critical', 'emergency',
                'alert', 'notice', 'log'))
            ->getMock();

        ReflectiveTools::overrideLogger($logger);

        return $logger;
    }

    public function testGetTreatmentWithNullMatchingKeyObject()
    {
        $this->expectException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatment(new Key(null, 'some_bucketing_key'), 'some_feature'));
    }

    public function testGetTreatmentWithEmptyMatchingKeyObject()
    {
        $this->expectException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatment(new Key('', 'some_bucketing_key'), 'some_feature'));
        $this->assertNotEquals('control', $splitSdk->getTreatment(new Key("0", 'some_bucketing_key'), 'some_feature'));
    }

    public function testGetTreatmentWithWrongTypeMatchingKeyObject()
    {
        $this->expectException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatment(new Key(true, 'some_bucketing_key'), 'some_feature'));
    }

    public function testGetTreatmentWithNonFiniteMatchingKeyObject()
    {
        $this->expectException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatment(new Key(log(0), 'some_bucketing_key'), 'some_feature'));
    }

    public function testGetTreatmentWitNumberMatchingKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("Key: matchingKey '12345' is not of type string, converting."),
                $this->equalTo("getTreatment: you passed some_feature that does not exist in this environment, "
                    . "please double check what feature flags exist in the Split user interface.")
            ));

        $this->assertEquals(
            'control',
            $splitSdk->getTreatment(new Key(12345, 'some_bucketing_key'), 'some_feature')
        );
    }

    public function testGetTreatmentWithNullBucketingKeyObject()
    {
        $this->expectException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatment(new Key('some_matching_key', null), 'some_feature'));
    }

    public function testGetTreatmentWithEmptyBucketingKeyObject()
    {
        $this->expectException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatment(new Key('some_matching_key', ''), 'some_feature'));
    }

    public function testGetTreatmentWithWrongTypeBucketingKeyObject()
    {
        $this->expectException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatment(new Key('some_matching_key', array()), 'some_feature'));
    }

    public function testGetTreatmentWitNonFiniteBucketingKeyObject()
    {
        $this->expectException('\SplitIO\Exception\KeyException');

        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatment(new Key('some_matching_key', log(0)), 'some_feature'));
    }

    public function testGetTreatmentWitNumberBucketingKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("Key: bucketingKey '12345' is not of type string, converting."),
                $this->equalTo("getTreatment: you passed some_feature that does not exist in this environment, "
                    . "please double check what feature flags exist in the Split user interface.")
            ));

            $this->assertEquals(
                'control',
                $splitSdk->getTreatment(new Key('some_matching_key', 12345), 'some_feature')
            );
    }

    public function testGetTreatmentKeyLong()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("getTreatment: key too long - must be 250 characters or less."));

        $this->assertEquals('control', $splitSdk->getTreatment('somekeysomekeysomekeysomekey' .
            'somekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekey' .
            'somekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekey' .
            'somekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysom', 'some_feature'));
    }

    public function testGetTreatmentWithEmptyKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("getTreatment: you passed an empty key, key must be a non-empty string."));

        $this->assertEquals('control', $splitSdk->getTreatment('', 'some_feature'));
    }

    public function testGetTreatmentWitNonFiniteKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('critical')
            ->with($this->equalTo("getTreatment: you passed an invalid key type, key must be a non-empty string."));

        $this->assertEquals('control', $splitSdk->getTreatment(log(0), 'some_feature'));
    }

    public function testGetTreatmentWitNumberKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("getTreatment: key '123456' is not of type string, converting."),
                $this->equalTo("getTreatment: you passed some_feature that does not exist in this environment, "
                    . "please double check what feature flags exist in the Split user interface.")
            ));

        $this->assertEquals('control', $splitSdk->getTreatment(123456, 'some_feature'));
    }

    public function testGetTreatmentWithEmptyFeatureName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("getTreatment: you passed an empty featureFlagName, flag name must be a non-empty"
                . " string."));

        $this->assertEquals('control', $splitSdk->getTreatment('some_key', ''));
    }

    public function testGetTreatmentWithFeatureNameWithWhitespaces()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("getTreatment: key '123456' is not of type string, converting."),
                $this->equalTo("getTreatment: you passed some_feature that does not exist in this environment, "
                    . "please double check what feature flags exist in the Split user interface.")
            ));

        $this->assertEquals('control', $splitSdk->getTreatment("some_key", 'some_feature  '));
    }

    public function testGetTreatmentWithFeatureNameWithWhitespaces2()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo('getTreatment: featureFlagName "   some_feature  " has extra whitespace, trimming.')
            ));

        $this->assertEquals('control', $splitSdk->getTreatment("some_key", '   some_feature  '));
    }

    public function testGetTreatmentWithNotExistantSplitName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo("getTreatment: you passed some_feature_non_existant that does not exist in this"
                . " environment, please double check what feature flags exist in the Split user interface."));

        $this->assertEquals('control', $splitSdk->getTreatment('some_key_non_existant', 'some_feature_non_existant'));
    }

    public function testGetTreatmentWithConfigWithNotExistantSplitName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo("getTreatmentWithConfig: you passed some_feature_non_existant that does"
                . " not exist in this environment, please double check what feature flags exist in the Split user interface."));

        $result = $splitSdk->getTreatmentWithConfig('some_key_non_existant', 'some_feature_non_existant');
        $this->assertEquals('control', $result['treatment']);
    }

    public static function tearDownAfterClass(): void
    {
        Utils\Utils::cleanCache();
    }
}
