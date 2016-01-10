<?php

namespace SplitIO\Test;

use SplitIO\Http\Request;
use SplitIO\Http\Method;

/**
 * @covers SplitIO\Http\Psr7\Request
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestUriMayBeString()
    {
        $r = new Request(Method::GET(), '/');
        $this->assertEquals('/', (string) $r->getUri());
    }
}