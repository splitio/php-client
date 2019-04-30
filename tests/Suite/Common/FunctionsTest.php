<?php
namespace SplitIO\Test\Suite\Common;

use SplitIO\Component\Utils as SplitIOUtils;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{

    public function testParseSplitsFileFunction()
    {
        $splitFileContent = <<<SFC
#This is a comment
feature_A treatment_1
   #This is a comment whit blanks
feature_B treatment_2
feature_C treatment_1
SFC;

        $parsed = \SplitIO\parseSplitsFile($splitFileContent);

        $this->assertEquals('treatment_1', $parsed['feature_A']['treatment']);
        $this->assertEquals('treatment_2', $parsed['feature_B']['treatment']);
        $this->assertEquals('treatment_1', $parsed['feature_C']['treatment']);
    }

    public function testIsAssociativeArrayWithEmptyAssociativeArray()
    {
        $this->assertTrue(SplitIOUtils\isAssociativeArray(array()));
    }

    public function testIsAssociativeArrayWithEmptyArray()
    {
        $this->assertTrue(SplitIOUtils\isAssociativeArray(array()));
    }

    public function testIsAssociativeArrayWithIndexedArray()
    {
        $this->assertFalse(SplitIOUtils\isAssociativeArray(array(1, 2, 3, 4)));
    }

    public function testIsAssociativeArrayWithAssociativeArray()
    {
        $this->assertTrue(SplitIOUtils\isAssociativeArray(array('one' => 'one', 'two' => null)));
    }
}
