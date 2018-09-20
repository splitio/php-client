<?php

namespace SplitIO\Test\Suite\Sdk\Helpers;

class ListenerClient implements \SplitIO\Sdk\ImpressionListener
{
    public $dataLogged;

    public function logImpression($data)
    {
        $this->dataLogged = $data;
    }
}
