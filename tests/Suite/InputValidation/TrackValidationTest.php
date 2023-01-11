<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Test\Suite\Redis\ReflectiveTools;
use SplitIO\Sdk\Validator\InputValidator;

use SplitIO\Test\Utils;

class TrackValidationTest extends \PHPUnit\Framework\TestCase
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
            ->setMethods(array('warning', 'debug', 'error', 'info', 'critical', 'emergency',
                'alert', 'notice', 'write', 'log'))
            ->getMock();

        ReflectiveTools::overrideLogger($logger);

        return $logger;
    }

    public function testTrackWithNullKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed a null key, key must be a non-empty string."));

        $this->assertEquals(false, $splitSdk->track(null, 'some_traffic', 'some_event', 1));
    }

    public function testTrackKeyLong()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: key too long - must be 250 characters or less."));

        $this->assertEquals(false, $splitSdk->track('somekeysomekeysomekeysomekey' .
            'somekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekey' .
            'somekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekey' .
            'somekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysomekeysom', '1', '1', 1));
    }

    public function testTrackWitNumberKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("track: key '123456' is not of type string, converting."),
                $this->equalTo("track: Traffic Type 'some_traffic' does not have any corresponding Splits "
                    . "in this environment, make sure you’re tracking your events to a valid traffic "
                    . "type defined in the Split console.")
            ));

        $this->assertEquals(true, $splitSdk->track(123456, 'some_traffic', 'some_event', 1));
    }

    public function testTrackWithEmptyKey()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed an empty key, key must be a non-empty string."));

        $this->assertEquals(false, $splitSdk->track('', 'some_traffic', 'some_event', 1));
    }

    public function testTrackWitKeyDifferentFromNumberObjectOrString()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed an invalid key type, key must be a non-empty string."));

        $this->assertEquals(false, $splitSdk->track(true, 'some_traffic', 'some_event', 1));
    }

    public function testTrackWithNullTrafficType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed a null traffic type, traffic type must be a non-empty string."));

        $this->assertEquals(false, $splitSdk->track('some_key', null, 'some_event', 1));
    }

    public function testTrackWithBooleanTraficType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed an invalid traffic type, traffic type must be a non-empty "
                . "string."));

        $this->assertEquals(false, $splitSdk->track('some_key', true, 'some_event', 1));
    }

    public function testTrackWithArrayTraficType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed an invalid traffic type, traffic type must be a non-empty "
                . "string."));
        
        $this->assertEquals(false, $splitSdk->track('some_key', array(), 'some_event', 1));
    }

    public function testTrackWithNumberTraficType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed an invalid traffic type, traffic type must be a non-empty "
                . "string."));
        
        $this->assertEquals(false, $splitSdk->track('some_key', 12345, 'some_event', 1));
    }

    public function testTrackWithEmptyTraficType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed an empty traffic type, traffic type must be a non-empty "
                . "string."));

        $this->assertEquals(false, $splitSdk->track('some_key', '', 'some_event', 1));
    }

    public function testTrackNotLowercaseTrafficType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
        ->method('warning')
        ->with($this->logicalOr(
            $this->equalTo("track: 'UPPERCASE' should be all lowercase - converting string to lowercase."),
            $this->equalTo("track: Traffic Type 'uppercase' does not have any corresponding Splits "
                . "in this environment, make sure you’re tracking your events to a valid traffic "
                . "type defined in the Split console.")
        ));

        $this->assertEquals(true, $splitSdk->track('some_key', 'UPPERCASE', 'some_event', 1));
    }

    public function testTrackDoesNotMatchesTrafficTypeName()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
        ->method('warning')
        ->with($this->equalTo("track: Traffic Type 'not_match' does not have any corresponding Splits "
            . "in this environment, make sure you’re tracking your events to a valid traffic "
            . "type defined in the Split console."));

        $this->assertEquals(true, $splitSdk->track('some_key', 'not_match', 'some_event', 1));
    }

    public function testTrackWithNullEventType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed a null event type, event type must be a non-empty string."));

        $this->assertEquals(false, $splitSdk->track('some_key', 'some_traffic', null, 1));
    }

    public function testTrackWithEmptyEventType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed an empty event type, event type must be a non-empty string."));

        $this->assertEquals(false, $splitSdk->track('some_key', 'some_traffic', '', 1));
    }

    public function testTrackWithBooleanEventType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed an invalid event type, event type must be a non-empty string."));

        $this->assertEquals(false, $splitSdk->track('some_key', 'some_traffic', true, 1));
    }

    public function testTrackWithArrayEventType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed an invalid event type, event type must be a non-empty string."));

        $this->assertEquals(false, $splitSdk->track('some_key', 'some_traffic', array(), 1));
    }

    public function testTrackWithNumberEventType()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo("track: you passed an invalid event type, event type must be a non-empty string."));

        $this->assertEquals(false, $splitSdk->track('some_key', 'some_traffic', 12345, 1));
    }

    public function testTrackWithEventTypeDoesNotConformWithRegExp()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('track: eventType must adhere to the regular expression '
                . '/^[a-zA-Z0-9][-_.:a-zA-Z0-9]{0,79}$/. This means an event name must be alphanumeric, '
                . 'cannot be more than 80 characters long, and can only include a dash, underscore, '
                . 'period, or colon as separators of alphanumeric characters.'));

        $this->assertEquals(false, $splitSdk->track('some_key', 'some_traffic', '@@', 1));
    }

    public function testTrackWithNullValue()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $this->assertEquals(true, $splitSdk->track('some_key', 'some_traffic', 'some_event', null));
    }

    public function testTrackWithBooleanValue()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('track: value must be a number.'));

        $this->assertEquals(false, $splitSdk->track('some_key', 'some_traffic', 'some_event', true));
    }

    public function testTrackWithStringValue()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('track: value must be a number.'));

        $this->assertEquals(false, $splitSdk->track('some_key', 'some_traffic', 'some_event', 'string'));
    }

    public function testTrackWithIntValue()
    {
        $splitSdk = $this->getFactoryClient();

        $this->assertEquals(true, $splitSdk->track('some_key', 'some_traffic', 'some_event', 1));
    }

    public function testTrackWithFloatValue()
    {
        $splitSdk = $this->getFactoryClient();

        $this->assertEquals(true, $splitSdk->track('some_key', 'some_traffic', 'some_event', 1.4));
    }

    public function testInputValidationProperties()
    {
        $this->assertEquals(null, InputValidator::validProperties(null));

        $this->assertEquals(null, InputValidator::validProperties(array(1 => "two")));

        $this->assertEquals(null, InputValidator::validProperties(array()));

        $this->assertEquals(false, InputValidator::validProperties(array(1, "two")));

        $this->assertEquals(false, InputValidator::validProperties(1));

        $properties1 = array(
            "test1" => "test",
            "test2" => 1,
            "test3" => true,
            "test4" => null,
            "test5" => array(),
            2 => "t",
        );
        $propertiesResult = InputValidator::validProperties($properties1);

        $this->assertEquals(5, count($propertiesResult));
        $this->assertEquals("test", $propertiesResult["test1"]);
        $this->assertEquals(1, $propertiesResult["test2"]);
        $this->assertEquals(true, $propertiesResult["test3"]);
        $this->assertEquals(null, $propertiesResult["test4"]);
        $this->assertEquals(null, $propertiesResult["test5"]);

        $properties2 = array();
        for ($i = 1; $i <= 301; $i++) {
            $properties2["props" . strval($i)] = $i;
        }
        $this->assertEquals($properties2, InputValidator::validProperties($properties2));

        $properties3 = array();
        for ($i = 1; $i <= 110; $i++) {
            $properties3[strval($i)] = str_pad("", 300, "a", STR_PAD_LEFT);
        }
        $this->assertEquals(false, InputValidator::validProperties($properties3));
    }

    public function testTrackWithNullProperties()
    {
        $splitSdk = $this->getFactoryClient();

        $this->assertEquals(true, $splitSdk->track('some_key', 'some_traffic', 'some_event', 1, null));
    }

    public function testTrackWithAssociativeArratWithoutStringProperties()
    {
        $splitSdk = $this->getFactoryClient();

        $this->assertEquals(true, $splitSdk->track(
            'some_key',
            'some_traffic',
            'some_event',
            1,
            array(1 => "two")
        ));
    }

    public function testTrackWithEmptyProperties()
    {
        $splitSdk = $this->getFactoryClient();

        $this->assertEquals(true, $splitSdk->track('some_key', 'some_traffic', 'some_event', 1, array()));
    }

    public function testTrackWithInvalidArrayProperties()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('track: properties must be of type associative array.'));

        $this->assertEquals(false, $splitSdk->track(
            'some_key',
            'some_traffic',
            'some_event',
            1,
            array(1, true, "two")
        ));
    }

    public function testTrackWithInvalidProperties()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('track: properties must be of type associative array.'));

        $this->assertEquals(false, $splitSdk->track('some_key', 'some_traffic', 'some_event', 1, true));
    }

    public function testTrackWithProperties()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("Property [] is of invalid type. Setting value to null"),
                $this->equalTo("track: Traffic Type 'some_traffic' does not have any corresponding Splits "
                    . "in this environment, make sure you’re tracking your events to a valid traffic "
                    . "type defined in the Split console.")
            ));

        $properties = array(
            "test1" => "test",
            "test2" => 1,
            "test3" => true,
            "test4" => null,
            "test5" => array(),
            2 => "t",
        );

        $this->assertEquals(true, $splitSdk->track('some_key', 'some_traffic', 'some_event', 1, $properties));
    }

    public function testTrackWithPropertiesGreaterThan300Properties()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->any())
            ->method('warning')
            ->with($this->logicalOr(
                $this->equalTo("Event has more than 300 properties. Some of them will be trimmed when processed"),
                $this->equalTo("track: Traffic Type 'some_traffic' does not have any corresponding Splits "
                    . "in this environment, make sure you’re tracking your events to a valid traffic "
                    . "type defined in the Split console.")
            ));

        $properties = array();
        for ($i = 1; $i <= 301; $i++) {
            $properties["prop" . strval($i)] = $i;
        }

        $this->assertEquals(true, $splitSdk->track('some_key', 'some_traffic', 'some_event', 1, $properties));
    }

    public function testTrackWithEventGreaterThan32KB()
    {
        $splitSdk = $this->getFactoryClient();

        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->equalTo('The maximum size allowed for the properties is 32768 bytes. '
                . 'Current one is 32844 bytes. Event not queued'));

        $properties = array();
        for ($i = 1; $i <= 110; $i++) {
            $properties["prop" . strval($i)] = str_pad("", 300, "a", STR_PAD_LEFT);
        }

        $this->assertEquals(false, $splitSdk->track('some_key', 'some_traffic', 'some_event', 1, $properties));
    }

    public static function tearDownAfterClass(): void
    {
        Utils\Utils::cleanCache();
    }
}
