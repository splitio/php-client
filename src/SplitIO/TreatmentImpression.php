<?php
namespace SplitIO;

use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Component\Common\Di;

class TreatmentImpression
{
    /**
     * @param \SplitIO\Sdk\Impressions\Impression $impression
     * @return bool
     */
    public static function log(Impression $impression)
    {
        try {
            Di::getLogger()->debug($impression);

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
            Di::getLogger()->warning('Unable to write impression back to redis.');
            Di::getLogger()->warning($e->getMessage());
        }
    }
}
