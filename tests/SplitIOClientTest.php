<?php
namespace SplitIO\Test;

class SplitIOClientTest extends \PHPUnit_Framework_TestCase
{
    public function connectionTest()
    {
        $this->assertTrue(true);
    }

    /**
     * @depends testOne
     */
    public function dataTest()
    {
    }
}