<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Component\Common\Di;

class GetTreatmentValidationTest extends \PHPUnit_Framework_TestCase
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

    public function testGetTreatmentWithNullKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatment: key cannot be null.'));

        $this->assertEquals('control', $splitSdk->getTreatment(null, 'some_feature'));
    }

    public function testGetTreatmentWitNumberKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo('getTreatment: key 123456 is not of type string, converting.')
            ));

        $this->assertEquals('control', $splitSdk->getTreatment(123456, 'some_feature'));
    }

    public function testGetTreatmentWitKeyDifferentFromNumberObjectOrString()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatment: key has to be of type "string" or "SplitIO\Sdk\Key".'));

        $this->assertEquals('control', $splitSdk->getTreatment(true, 'some_feature'));
    }

    public function testGetTreatmentWithNullFeatureName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatment: featureName cannot be null.'));

        $this->assertEquals('control', $splitSdk->getTreatment('some_key', null));
    }

    public function testGetTreatmentWithNumericFeatureName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatment: featureName has to be of type "string".'));

        $this->assertEquals('control', $splitSdk->getTreatment('some_key', 12345));
    }

    public function testGetTreatmentWitBooleanFeatureName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatment: featureName has to be of type "string".'));

        $this->assertEquals('control', $splitSdk->getTreatment('some_key', true));
    }

    public function testGetTreatmentWitArrayFeatureName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatment: featureName has to be of type "string".'));

        $this->assertEquals('control', $splitSdk->getTreatment('some_key', array()));
    }

    public function testGetTreatmentWithValidInputs()
    {
        $splitSdk = $this->getFactoryClient();

        $this->assertEquals('control', $splitSdk->getTreatment('some_key_non_existant', 'some_feature_non_existant'));
    }
}
