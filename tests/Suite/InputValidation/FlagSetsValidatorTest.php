<?php
namespace SplitIO\Test\Suite\InputValidation;

use SplitIO\Component\Common\Di;
use SplitIO\Sdk\Validator\FlagSetsValidator;

class FlagSetsValidatorTest extends \PHPUnit\Framework\TestCase
{
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

    public function testAreValidWithEmptyArray()
    {
        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('error')
            ->with($this->equalTo('test: FlagSets must be a non-empty list.'));

        $result = FlagSetsValidator::areValid([], "test");
        $this->assertEquals(0, count($result));
    }

    public function testAreValidWithWhitespaces()
    {
        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo('test: Flag Set name "    set_1 " has extra whitespace, trimming.'));

        $result = FlagSetsValidator::areValid(["    set_1 "], "test");
        $this->assertEquals(1, count($result));
    }

    public function testAreValidWithUppercases()
    {
        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo('test: Flag Set name "SET_1" should be all lowercase - converting string to lowercase.'));

        $result = FlagSetsValidator::areValid(["SET_1"], "test");
        $this->assertEquals(1, count($result));
    }

    public function testAreValidWithIncorrectCharacters()
    {
        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo('test: you passed "set-2", Flag Set must adhere to the regular expressions {/^[a-z0-9][_a-z0-9]{0,49}$/} This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and have a max length of 50 characters. "set-2" was discarded.'));

        $result = FlagSetsValidator::areValid(["set-2"], "test");
        $this->assertEquals(0, count($result));
    }

    public function testAreValidWithFlagSetToLong()
    {
        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('warning')
            ->with($this->equalTo('test: you passed "set_123123123123123123123123123123123123123123123123", Flag Set must adhere to the regular expressions {/^[a-z0-9][_a-z0-9]{0,49}$/} This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and have a max length of 50 characters. "set_123123123123123123123123123123123123123123123123" was discarded.'));

        $result = FlagSetsValidator::areValid(["set_123123123123123123123123123123123123123123123123"], "test");
        $this->assertEquals(0, count($result));
    }

    public function testAreValidWithFlagSetDupiclated()
    {
        $result = FlagSetsValidator::areValid(["set_4", "set_1", "SET_1", "set_2", " set_2 ", "set_3", "set_3"], "test");
        $this->assertEquals(4, count($result));
        $this->assertEquals("set_4", $result[0]);
        $this->assertEquals("set_1", $result[1]);
        $this->assertEquals("set_2", $result[2]);
        $this->assertEquals("set_3", $result[3]);
    }

    public function testAreValidWithIncorrectTypes()
    {
        $logger = $this->getMockedLogger();

        $logger->expects($this->once())
            ->method('error')
            ->with($this->equalTo('test: FlagSet must be a string and not null. 123 was discarded.'));

        $result = FlagSetsValidator::areValid([null, 123, "set_1", "SET_1"], "test");
        $this->assertEquals(1, count($result));
    }

    public function testAreValidConsecutive()
    {
        $logger = $this->getMockedLogger();

        $logger
            ->expects($this->exactly(6))
            ->method('warning')
            ->withConsecutive(
                ['test: Flag Set name "   A  " has extra whitespace, trimming.'],
                ['test: Flag Set name "   A  " should be all lowercase - converting string to lowercase.'],
                ['test: Flag Set name "@FAIL" should be all lowercase - converting string to lowercase.'],
                ['test: you passed "@FAIL", Flag Set must adhere to the regular expressions ' .
                    '{/^[a-z0-9][_a-z0-9]{0,49}$/} This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and ' .
                    'have a max length of 50 characters. "@FAIL" was discarded.'],
                ['test: Flag Set name "TEST" should be all lowercase - converting string to lowercase.'],
                ['test: Flag Set name "  a" has extra whitespace, trimming.'],
            );
        $logger
            ->expects($this->exactly(2))
            ->method('error')
            ->withConsecutive(
                ['test: FlagSets must be a non-empty list.'],
                ['test: FlagSets must be a non-empty list.']
            );

        $this->assertEquals(['a', 'test'], FlagSetsValidator::areValid(['   A  ', '@FAIL', 'TEST'], 'test'));
        $this->assertEquals(array(), FlagSetsValidator::areValid([], 'test'));
        $this->assertEquals(array(), FlagSetsValidator::areValid(['some' => 'some1'], 'test'));
        $this->assertEquals(['a', 'test'], FlagSetsValidator::areValid(['a', 'test', '  a'], 'test'));
    }
}
