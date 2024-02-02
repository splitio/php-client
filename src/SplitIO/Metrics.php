<?php

namespace SplitIO;

use SplitIO\Component\Stats\Latency;

class Metrics
{
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
