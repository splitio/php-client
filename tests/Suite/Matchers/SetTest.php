<?php
namespace SplitIO\Test\Suite\Matcher;

use SplitIO\Grammar\Condition\Matcher\DataType\Set;

class SetTest extends \PHPUnit\Framework\TestCase
{
    public function testSetBasic()
    {
        $set = new Set();
        $set->add('abc');
        $set->add('qwe');
        $set->add('asd');
        $set->add('abc');
        $this->assertEquals($set->count(), 3);
        $this->assertEquals($set->toArray(), array('abc', 'qwe', 'asd'));
        $this->assertTrue($set->has('abc'));
        $this->assertTrue($set->has('qwe'));
        $this->assertTrue($set->has('asd'));
        $this->assertFalse($set->has('zzz'));
    }

    public function addRemove()
    {
        $set = new Set();
        $set->add('abc');
        $set->add('qwe');
        $set->add('asd');
        $set->add('abc');
        $set->remove('qwe');
        $set->remove('asd');
        $this->assertEquals($set->count(), 1);
        $this->assertEquals($set->toArray(), array('abc'));
    }

    public function testFromArray()
    {
        $arr = array('abc', 'qwe', 'asd', 'abc');
        $set = Set::fromArray($arr);
        $this->assertEquals($set->count(), 3);
        $this->assertEquals($set->toArray(), array('abc', 'qwe', 'asd'));
    }

    public function testToArray()
    {
        $set = new Set();
        $set->add('abc');
        $set->add('qwe');
        $set->add('asd');
        $set->add('abc');
        $arr = $set->toArray();
        $this->assertEquals(count($arr), 3);
        $this->assertEquals($arr, array('abc', 'qwe', 'asd'));
    }

    public function testIntersect()
    {
        $set1 = Set::fromArray(array('qwe', 'asd', 'zxc'));
        $set2 = Set::fromArray(array('asd', 'rty', 'fgh'));
        $set3 = $set1->intersect($set2);
        $set4 = $set2->intersect($set1);
        $this->assertEquals($set3->toArray(), $set4->toArray());
        $this->assertEquals($set3->count(), $set4->count());
        $this->assertEquals($set3->toArray(), array('asd'));
        $this->assertEquals($set3->count(), 1);
    }

    public function testUnion()
    {
        $set1 = Set::fromArray(array('qwe', 'asd', 'zxc'));
        $set2 = Set::fromArray(array('asd', 'rty', 'fgh'));
        $set3 = $set1->union($set2);
        $set4 = $set2->union($set1);
        $this->assertEquals($set3->count(), $set4->count());
        $this->assertEquals($set3->count(), 5);
        $this->assertTrue($set3->has('qwe'));
        $this->assertTrue($set3->has('asd'));
        $this->assertTrue($set3->has('zxc'));
        $this->assertTrue($set3->has('rty'));
        $this->assertTrue($set3->has('fgh'));
    }

    public function testIsSubsetOf()
    {
        $this->assertTrue(Set::fromArray(array('qwe'))->isSubSetOf(Set::fromArray(array('qwe', 'asd', 'zxc'))));
        $this->assertTrue(
            Set::fromArray(array('qwe', 'asd', 'zxc'))->isSubSetOf(Set::fromArray(array('qwe', 'asd', 'zxc')))
        );
        $this->assertTrue(Set::fromArray(array())->isSubSetOf(Set::fromArray(array('qwe', 'asd', 'zxc'))));
        $this->assertFalse(Set::fromArray(array('qwe', 'asd', 'zxc'))->isSubsetOf(Set::fromArray(array('qwe'))));
    }

    public function testEquals()
    {
        $this->assertTrue(Set::fromArray(array('qwe'))->equals(Set::fromArray(array('qwe'))));
        $this->assertTrue(Set::fromArray(array('qwe', 'asd'))->equals(Set::fromArray(array('qwe', 'asd'))));
        $this->assertFalse(Set::fromArray(array('qwe'))->equals(Set::fromArray(array('asd'))));
    }
}
