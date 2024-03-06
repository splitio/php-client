<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Test\Suite\Redis\ReflectiveTools;

class ManagerValidationTest extends \PHPUnit\Framework\TestCase
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
        $splitSdk = $splitFactory->manager();

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

    public function testManagerWithEmptySplitName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("split: you passed an empty featureFlagName, flag name must be a non-empty string."));

        $this->assertEquals(null, $splitSdk->split(''));
    }

    public function testManagerWithValidFeatureName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();
        
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo("split: you passed this_is_a_non_existing_split that does"
                . " not exist in this environment, please double check what feature flags exist in the Split user interface."));

        $this->assertEquals(null, $splitSdk->split('this_is_a_non_existing_split'));
    }
}
