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
        $this->assertEquals($expected, $given );
    }

}
