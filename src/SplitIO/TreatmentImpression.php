<?php
namespace SplitIO;

use SplitIO\Component\Cache\ImpressionCache;

class TreatmentImpression
{
    /**
     * @param string $key
     * @param string $featureName
     * @param string $treatment
     * @param long $time
     * @return bool
     */
    public static function log($key, $featureName, $treatment, $time = null)
    {
        if ($time === null || !is_integer($time)) {
            $dateTimeUTC = new \DateTime("now", new \DateTimeZone("UTC"));
            $milliseconds = $dateTimeUTC->getTimestamp();
        } else {
            $milliseconds = $time;
        }

        $milliseconds = $milliseconds * 1000;

        $impressionCache = new ImpressionCache();
        return $impressionCache->addDataToFeature($featureName, $key, $treatment, $milliseconds);
    }
}
