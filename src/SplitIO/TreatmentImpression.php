<?php
namespace SplitIO;

use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Sdk\Impressions\Impression;
use SplitIO\Component\Common\Di;

class TreatmentImpression
{
    /**
     * @param \SplitIO\Sdk\Impressions\Impression $impressions
     * @return bool
     */
    public static function log($impressions)
    {
        try {
            Di::getLogger()->debug($impressions);

            $impressionCache = new ImpressionCache();
            $toStore = (is_array($impressions)) ? $impressions : array($impressions);
            return $impressionCache->logImpressions(
                $toStore,
                array(
                    'sdkVersion' => 'php-' . \SplitIO\version(),
                    'machineIp' => \SplitIO\getHostIpAddress(),
                    'machineName' => null, // TODO
                )
            );
        } catch (\Exception $e) {
            Di::getLogger()->warning('Unable to write impression back to redis.');
            Di::getLogger()->warning($e->getMessage());
        }
    }
}
