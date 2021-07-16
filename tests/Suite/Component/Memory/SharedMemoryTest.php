<?php
namespace SplitIO\Test\Suite\Component\Memory;

use SplitIO\Component\Memory\SharedMemory;

class SharedMemoryTest extends \PHPUnit\Framework\TestCase
{
    public function testSharedMemoryOperations()
    {
        if (!extension_loaded('shmop')) {
            $this->markTestSkipped(
                'The shmop extension is not available.'
            );
        }

        $key = 1234;
        $value = 'bar';

        SharedMemory::write($key, $value);
        $given = SharedMemory::read($key);
        $this->assertEquals($value, $given);

        $updated_value = 'new_value';
        SharedMemory::write($key, $updated_value);
        $given = SharedMemory::read($key);
        $this->assertEquals($updated_value, $given);
    }
}
