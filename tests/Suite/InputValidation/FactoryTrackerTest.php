<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Component\Common\Di;

class FactoryTrackerTest extends \PHPUnit\Framework\TestCase
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

        Di::set(Di::KEY_LOG, $logger);

        return $logger;
    }

    public function testMultipleClientInstantiation()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        $splitFactory = $this->getFactoryClient();
        $this->assertNotNull($splitFactory->client());

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("Factory Instantiation: creating multiple factories is not possible. "
                . "You have already created a factory."));
        
        $splitFactory2 = $this->getFactoryClient();
        $this->assertEquals(null, $splitFactory2);
    }
}
