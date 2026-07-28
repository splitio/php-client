<?php

namespace SplitIO\Test\Suite\Sdk\Helpers;

class ImpressionsDisabledE2EListener implements \SplitIO\Sdk\ImpressionListener
{
    public $receivedImpressions = array();

    public function logImpression($bundle)
    {
        // Bundle is an array with 'impression', 'attributes', 'instance-id', 'sdk-language-version'
        $impression = $bundle['impression'];
        $this->receivedImpressions[] = array(
            'feature' => $impression->getFeature(),
            'key' => $impression->getId(),
            'treatment' => $impression->getTreatment(),
            'label' => $impression->getLabel(),
            'changeNumber' => $impression->getChangeNumber()
        );
    }

    public function close()
    {
    }

    public function reset()
    {
        $this->receivedImpressions = array();
    }
}
