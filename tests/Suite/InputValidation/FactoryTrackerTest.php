<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Test\Suite\Redis\ReflectiveTools;

class FactoryTrackerTest extends \PHPUnit\Framework\TestCase
{
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

    public function testMultipleClientInstantiation()
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
        $this->assertNotNull($splitFactory->client());

        $logger = $this->getMockedLogger();
        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("Factory Instantiation: You already have 1 factory/factories with this API Key. "
                    . "We recommend keeping only one instance of the factory at all times (Singleton pattern) and reusing "
                    . "it throughout your application."),
                $this->equalTo("Factory Instantiation: You already have 2 factory/factories with this API Key. "
                    . "We recommend keeping only one instance of the factory at all times (Singleton pattern) and reusing "
                    . "it throughout your application."),
                $this->equalTo("Factory Instantiation: You already have an instance of the Split factory. "
                    . "Make sure you definitely want this additional instance. We recommend keeping only one instance "
                    . "of the factory at all times (Singleton pattern) and reusing it throughout your application.")
            )
        );

        //Initializing the SDK instance2.
        $splitFactory2 = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $this->assertNotNull($splitFactory2->client());

        //Initializing the SDK instance3.
        $splitFactory3 = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $this->assertNotNull($splitFactory3->client());

        //Initializing the SDK instance4.
        $splitFactory4 = \SplitIO\Sdk::factory('other', $sdkConfig);
        $this->assertNotNull($splitFactory4->client());
    }
}
