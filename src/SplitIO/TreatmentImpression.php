<?php
namespace SplitIO;

use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Sdk\Impressions\Impression;

class TreatmentImpression
{
    /**
     * @param \SplitIO\Sdk\Impressions\Impression $impression
     * @return bool
     */
    public static function log(Impression $impression)
    {
        Split::logger()->debug($impression);

        $impressionCache = new ImpressionCache();
        return $impressionCache->addDataToFeature(
            $impression->getFeature(),
            $impression->getId(),
            $impression->getTreatment(),
            $impression->getTime()
        );
    }
}
