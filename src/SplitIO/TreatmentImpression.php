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
        try {
            Split::logger()->debug($impression);

            $impressionCache = new ImpressionCache();
            return $impressionCache->addDataToFeature(
                $impression->getFeature(),
                $impression->getId(),
                $impression->getTreatment(),
                $impression->getTime(),
                $impression->getChangeNumber(),
                $impression->getLabel(),
                $impression->getBucketingKey()
            );
        } catch (\Exception $e) {
            Split::logger()->warning('Unable to write impression back to redis.');
            Split::logger()->warning($e->getMessage());
        }
    }
}
