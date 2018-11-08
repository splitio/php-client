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
            return $impressionCache->logImpressions(
                array($impression),
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
