<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Component\Common\Di;

class FactoryTrackerTest extends \PHPUnit_Framework_TestCase
{
    private function getFactoryClient()
    {
        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory($this->getApiKey(), $this->getSdkConfig());

        return $splitFactory;
    }

    private function getSdkConfig()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        return array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );
    }

    private function getApiKey()
    {
        return 'asdqwe123456';
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
        Di::set(Di::KEY_FACTORY, false);
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

    public function testMultipleSingletonClientInstantiation()
    {
        Di::set(Di::KEY_FACTORY, false);
        $splitFactory = \SplitIO\Sdk::singleton($this->getApiKey(), $this->getSdkConfig());

        $this->assertNotNull($splitFactory->client());

        $logger = $this->getMockedLogger();
        $logger->expects($this->never())
            ->method('critical');

        $splitFactory2 = \SplitIO\Sdk::singleton($this->getApiKey(), $this->getSdkConfig());
        $this->assertSame($splitFactory, $splitFactory2);
        $this->assertNotNull($splitFactory2->client());
    }
}
