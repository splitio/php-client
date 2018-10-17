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
            ->with($this->equalTo('getTreatments: featureNames cannot be null.'));

        $this->assertEquals(null, $splitSdk->getTreatments('some_key', null, null));
    }

    public function testGetTreatmentsWithFeaturesNotArray()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('getTreatments: featureNames must be an array.'));

        $this->assertEquals(null, $splitSdk->getTreatments('some_key', true, null));
    }

    public function testGetTreatmentsWithEmptyFeatures()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo('getTreatments: featureNames is an empty array or has null values.'));

        $this->assertEquals([], $splitSdk->getTreatments('some_key', [], null));
    }

    public function testGetTreatmentsWithNullFeaturesNames()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo('getTreatments: featureNames is an empty array or has null values.'),
                $this->equalTo('getTreatments: null featureName was filtered.')
            ));

        $this->assertEquals([], $splitSdk->getTreatments('some_key', [null, null], null));
    }

    public function testGetTreatmentsWithOneWrongTypeOfFeaturesNames()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo('getTreatments: featureNames is an empty array or has null values.'),
                $this->equalTo('getTreatments: filtered featureName for not being string.')
            ));

        $this->assertEquals([], $splitSdk->getTreatments('some_key', [true, array()], null));
    }
}
