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
        $miliseconds = (($time == null) ? time() : $time) * 1000;
        return (new ImpressionCache())->addDataToFeature($featureName, $key, $treatment, $miliseconds);
    }
}
