<?php
namespace SplitIO\Test;

use SplitIO\Http\Response;

/**
 * @covers SplitIO\Http\Response
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testAddsDefaultCode()
    {
        $r = new Response(200);
        $this->assertSame(200, $r->getStatusCode());
    }

    public function testBodyConsistent()
    {
        $r = new Response(200, [], '0');
        $this->assertEquals('0', (string)$r->getBody());
    }

}