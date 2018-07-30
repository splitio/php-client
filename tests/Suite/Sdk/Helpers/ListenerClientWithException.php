<?php

namespace SplitIO\Test\Suite\Sdk\Helpers;

class ListenerClientWithException implements \SplitIO\Sdk\ImpressionListener
{
    public $dataLogged;

    public function logImpression($data)
    {
        throw Eception('Simulate an Exception');
    }
}
