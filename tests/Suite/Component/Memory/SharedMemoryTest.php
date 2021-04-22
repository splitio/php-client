<?php
namespace SplitIO\Test\Suite\Component\Memory;

use SplitIO\Component\Memory\SharedMemory as Sut;

class SharedMemoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testWrite()
    {
        if (!extension_loaded('shmop')) {
            $this->markTestSkipped(
                'The shmop extension is not available.'
            );
        }

        $key = 1234;
        $value = 'bar';

        Sut::write($key, $value);

        return [$key, $value];
    }

    /**
     * @depends testWrite
     */
    public function testRead(array $tuple)
    {
        list($key, $expected) = $tuple;

        $given = Sut::read($key);
        $this->assertEquals($expected, $given);
    }

    public function overwriteProvider()
    {
        return [
            'longer to shorter' => [4321, str_repeat('a', 50), 'b'],
            'shorter to longer' => [5555, 'c', 'ddddd'],
        ];
    }

    /**
     * @dataProvider overwriteProvider
     */
    public function testOverwrite($key, $original_value, $updated_value)
    {
        if (!extension_loaded('shmop')) {
            $this->markTestSkipped(
                'The shmop extension is not available.'
            );
        }

        Sut::write($key, $original_value);
        Sut::write($key, $updated_value);
        $given = Sut::read($key);
        $this->assertEquals($updated_value, $given);
    }

}
