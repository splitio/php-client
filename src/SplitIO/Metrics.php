<?php
namespace SplitIO;

use SplitIO\Component\Stats\Latency;

class Metrics
{
    const MNAME_SDK_GET_TREATMENT = 'sdk.getTreatment';
    const MNAME_SDK_GET_TREATMENT_WITH_CONFIG = 'sdk.getTreatmentWithConfig';
    const MNAME_SDK_GET_TREATMENTS = 'sdk.getTreatments';
    const MNAME_SDK_GET_TREATMENTS_WITH_CONFIG = 'sdk.getTreatmentsWithConfig';
    const MNAME_SDK_GET_TREATMENTS_WITH_CONFIG_BY_FLAG_SETS = 'sdk.getTreatmentsWithConfigByFlagSets';
    const MNAME_SDK_GET_TREATMENTS_BY_FLAG_SETS = 'sdk.getTreatmentsByFlagSets';

    public static function startMeasuringLatency()
    {
        return Latency::startMeasuringLatency();
    }

    public static function calculateLatency($timeStart)
    {
        return Latency::calculateLatency($timeStart);
    }

    /**
     * Returns the bucket that this latency falls into.
     * The latencies will not be updated.
     * @param latency
     * @return int the bucket content for the latency.
     */
    public static function getBucketForLatencyMicros($latency)
    {
        return Latency::getBucketForLatencyMicros($latency);
    }
}
