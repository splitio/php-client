<?php

namespace SplitIO\Test\Suite\Sdk\Helpers;

class ImpressionsDisabledListener implements \SplitIO\Sdk\ImpressionListener
{
    public $receivedImpressions = array();
    public $receivedAttributes = array();

    public function logImpression($impression)
    {
        $this->receivedImpressions[] = $impression;
    }

    public function close()
    {
    }

    public function reset()
    {
        $this->receivedImpressions = array();
        $this->receivedAttributes = array();
    }
}
