<?php

namespace SplitIO\Test;

use SplitIO\Http\Request;
use SplitIO\Http\MethodEnum;

/**
 * @covers SplitIO\Http\Psr7\Request
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestUriMayBeString()
    {
        $r = new Request(MethodEnum::GET(), '/');
        $this->assertEquals('/', (string) $r->getUri());
    }

    public function testRequestHeaders()
    {
        $r = new Request(MethodEnum::GET(), '/', ['X-Custom-Header-1'=>'Value of X-Custom-Header1']);
        $r->setHeader('X-Custom-Header-2', 'Value of X-Custom-Header2');

        $this->assertEquals('Value of X-Custom-Header1', $r->getHeader('X-Custom-Header-1'));
        $this->assertEquals('Value of X-Custom-Header2', $r->getHeader('X-Custom-Header-2'));
        $this->assertNull($r->getHeader('Invalid-Header'));

    }
}