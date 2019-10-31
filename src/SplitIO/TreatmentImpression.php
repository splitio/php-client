<?php
namespace SplitIO;

use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Component\Common\Di;
use SplitIO\Sdk\QueueMetadataMessage;

class TreatmentImpression
{
    /**
     * @param \SplitIO\Sdk\Impressions\Impression $impressions
     * @return bool
     */
    public static function log($impressions, QueueMetadataMessage $metadata)
    {
        try {
            Di::getLogger()->debug($impressions);
            if (is_null($impressions) || (is_array($impressions) && 0 == count($impressions))) {
                return null;
            }
            $impressionCache = new ImpressionCache();
            $toStore = (is_array($impressions)) ? $impressions : array($impressions);
            return $impressionCache->logImpressions(
                $toStore,
                $metadata
            );
        } catch (\Exception $e) {
            Di::getLogger()->warning('Unable to write impression back to redis.');
            Di::getLogger()->warning($e->getMessage());
        }
    }
}
