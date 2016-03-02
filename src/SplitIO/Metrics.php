<?php
namespace SplitIO;

class Metrics
{
    const MNAME_SDK_GET_TREATMENT = 'sdk.getTreatment';

    const MAX_LATENCY = 7481828;

    private static $buckets = [1000, 1500, 2250, 3375, 5063, 7594, 11391, 17086, 25629, 38443,
                        57665, 86498, 129746, 194620, 291929, 437894, 656841, 985261, 1477892, 2216838,
                        3325257, 4987885, 7481828];

    public static function startMeasuringLatency()
    {
        return microtime(true);
    }

    public static function calculateLatency($timeStart)
    {
        $timeEnd = microtime(true);

        //microseconds
        return ($timeEnd - $timeStart) * 1000000;
    }

    /**
     * Returns the bucket that this latency falls into.
     * The latencies will not be updated.
     * @param latency
     * @return int the bucket content for the latency.
     */
    public static function getBucketForLatencyMicros($latency)
    {
        $bucket = 0;
        foreach (self::$buckets as $k => $v) {
            if ($latency <= $v) {
                return $k;
            }
        }

        return count(self::$buckets) - 1;
    }
}
