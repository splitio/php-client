<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Component\Common\Di;

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
            ->method('warning')
            ->with($this->equalTo('getTreatments: null featureName was filtered.'));
        $logger->expects($this->at(1))
            ->method('warning')
            ->with($this->equalTo('getTreatments: null featureName was filtered.'));
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
            ->method('warning')
            ->with($this->equalTo('getTreatments: filtered featureName for not being string.'));
        $logger->expects($this->at(1))
            ->method('warning')
            ->with($this->equalTo('getTreatments: filtered featureName for not being string.'));
        $logger->expects($this->at(2))
            ->method('critical')
            ->with($this->equalTo('getTreatments: featureNames must be a non-empty array.'));
        
        $this->assertEquals(null, $splitSdk->getTreatments('some_key', [true, array()], null));
    }
}
