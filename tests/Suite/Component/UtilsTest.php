<?php

namespace SplitIO\Test\Suite\Component;

use SplitIO\Component\Utils as SplitIOUtils;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAssocWithEmptyAssociativeArray()
    {
        $this->assertTrue(SplitIOUtils\isAssoc(array()));
    }

    public function testIsAssocWithEmptyArray()
    {
        $this->assertTrue(SplitIOUtils\isAssoc([]));
    }

    public function testIsAssocWithIndexedArray()
    {
        $this->assertFalse(SplitIOUtils\isAssoc([1, 2, 3, 4]));
    }

    public function testIsAssocWithAssociativeArray()
    {
        $this->assertTrue(SplitIOUtils\isAssoc(['one' => 'one', 'two' => null]));
    }
}
