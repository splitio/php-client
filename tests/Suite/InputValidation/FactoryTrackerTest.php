<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Test\Suite\Redis\ReflectiveTools;

class FactoryTrackerTest extends \PHPUnit\Framework\TestCase
{
    private function getFactoryClient()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);

        return $splitFactory;
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

        ReflectiveTools::overrideLogger($logger);

        return $logger;
    }

    public function testMultipleClientInstantiation()
    {
        $splitFactory = $this->getFactoryClient();
        $this->assertNotNull($splitFactory->client());

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo("Factory Instantiation: You already have an instance of the Split factory. "
                . "Make sure you definitely want this additional instance. We recommend keeping only one instance "
                . "of the factory at all times (Singleton pattern) and reusing it throughout your application."));
        
        $splitFactory2 = $this->getFactoryClient();
        $this->assertNotNull($splitFactory2->client());
    }
}
