<?php
namespace SplitIO\Test\Suite\Common;

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

        $this->assertEquals('treatment_1', $parsed['feature_A']);
        $this->assertEquals('treatment_2', $parsed['feature_B']);
        $this->assertEquals('treatment_1', $parsed['feature_C']);
    }
}