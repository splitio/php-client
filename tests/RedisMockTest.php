<?php
namespace SplitIO\Test;

//use SplitIO\Cache\Storage\Mock\RedisMock;
use SplitIO\Test\Mock\RedisMock;

class RedisMockTest extends \PHPUnit_Framework_TestCase
{
    public function testStringValues()
    {
        $rMock = new RedisMock();
        $rMock->set('myKey', 'Value-for-myKey');
        $this->assertEquals('Value-for-myKey', $rMock->get('myKey'));

        $rMock->set('myKey2', 'Value-for-myKey');
        $this->assertEquals(2, $rMock->delete('myKey', 'myKey2'));
    }

    public function testSetsValues()
    {
        $rMock = new RedisMock();

        $this->assertEquals(2, $rMock->sAdd("mySetKey", 'set-v-1', 'set-v-2'));

        $this->assertTrue($rMock->sIsMember("mySetKey", 'set-v-1'));

        $this->assertFalse($rMock->sIsMember("mySetKey", 'set-v-3'));

        $this->assertEquals(1, $rMock->sAdd("mySetKey", 'set-v-3'));

        $this->assertTrue($rMock->sIsMember("mySetKey", 'set-v-3'));

        $this->assertEquals(2, $rMock->sRem("mySetKey", 'set-v-3', 'set-v-2', 'set-v-4'));

        $this->assertFalse($rMock->sIsMember("mySetKey", 'set-v-3'));

        $this->assertFalse($rMock->sIsMember("mySetKey", 'set-v-2'));

        $this->assertTrue($rMock->sIsMember("mySetKey", 'set-v-1'));

        $this->assertEquals(['set-v-1'], $rMock->sMembers("mySetKey"));
    }
}
