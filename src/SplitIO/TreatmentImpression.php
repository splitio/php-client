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
            $milliseconds = (new \DateTime("now", new \DateTimeZone("UTC")))->getTimestamp();
        } else {
            $milliseconds = $time;
        }

        $milliseconds = $milliseconds * 1000;

        return (new ImpressionCache())->addDataToFeature($featureName, $key, $treatment, $milliseconds);
    }
}
