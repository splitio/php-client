<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Component\Common\Di;

class ManagerValidationTest extends \PHPUnit\Framework\TestCase
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
        $splitSdk = $splitFactory->manager();

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

    public function testManagerWithNullSplitName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("split: you passed a null split name, split name must be a non-empty string."));

        $this->assertEquals(null, $splitSdk->split(null));
    }

    public function testManagerWithEmptySplitName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("split: you passed an empty split name, split name must be a non-empty string."));

        $this->assertEquals(null, $splitSdk->split(''));
    }

    public function testManagerWithBooleanSplitName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("split: you passed an invalid split name, split name must be a non-empty string."));

        $this->assertEquals(null, $splitSdk->split(true));
    }

    public function testManagerWithArraySplitName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("split: you passed an invalid split name, split name must be a non-empty string."));

        $this->assertEquals(null, $splitSdk->split(array()));
    }

    public function testManagerWithNumberSplitName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("split: you passed an invalid split name, split name must be a non-empty string."));

        $this->assertEquals(null, $splitSdk->split(1));
    }

    public function testManagerWithValidFeatureName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo("split: you passed this_is_a_non_existing_split that does"
                . " not exist in this environment, please double check what Splits exist in the web console."));

        $this->assertEquals(null, $splitSdk->split('this_is_a_non_existing_split'));
    }
}
