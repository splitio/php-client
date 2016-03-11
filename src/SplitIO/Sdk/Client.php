<?php
namespace SplitIO\Sdk;

use SplitIO\Component\Cache\MetricsCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Engine;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Grammar\Split;
use SplitIO\Metrics;
use SplitIO\TreatmentImpression;
use SplitIO\Split as SplitApp;

class Client implements ClientInterface
{
    /**
     * Returns the treatment to show this id for this feature.
     * The set of treatments for a feature can be configured
     * on the Split web console.
     * This method returns the string 'control' if:
     * <ol>
     *     <li>Any of the parameters were null</li>
     *     <li>There was an exception</li>
     *     <li>The SDK does not know this feature</li>
     *     <li>The feature was deleted through the web console.</li>
     * </ol>
     * 'control' is a reserved treatment, to highlight these
     * exceptional circumstances.
     *
     * <p>
     * The sdk returns the default treatment of this feature if:
     * <ol>
     *     <li>The feature was killed</li>
     *     <li>The id did not match any of the conditions in the
     * feature roll-out plan</li>
     * </ol>
     * The default treatment of a feature is set on the Split web
     * console.
     *
     * <p>
     * This method does not throw any exceptions.
     * It also never  returns null.
     *
     * @param $key
     * @param $featureName
     * @return string
     */
    public function getTreatment($key, $featureName)
    {
        $splitCacheKey = SplitCache::getCacheKeyForSplit($featureName);
        $splitCachedItem = SplitApp::cache()->getItem($splitCacheKey);

        if ($splitCachedItem->isHit()) {
            SplitApp::logger()->info("$featureName is present on cache");
            $splitRepresentation = $splitCachedItem->get();

            $split = new Split(json_decode($splitRepresentation, true));

            if ($split->killed()) {
                return $split->getDefaultTratment();
            }

            $timeStart = Metrics::startMeasuringLatency();
            $treatment = Engine::getTreatment($key, $split);
            $latency = Metrics::calculateLatency($timeStart);

            //If the given key doesn't match on any condition, default treatment is returned
            if ($treatment == null) {
                $treatment = $split->getDefaultTratment();
            }

            //Registering latency value
            MetricsCache::addLatencyOnBucket(
                Metrics::MNAME_SDK_GET_TREATMENT,
                Metrics::getBucketForLatencyMicros($latency)
            );

            SplitApp::logger()->info("*Treatment for $key in {$split->getName()} is: $treatment");

            //Logging treatment impressions
            TreatmentImpression::log($key, $featureName, $treatment);

            //Returning treatment.
            return $treatment;
        }

        TreatmentImpression::log($key, $featureName, TreatmentEnum::CONTROL);

        return TreatmentEnum::CONTROL;
    }

    /**
     * A short-hand for
     * <pre>
     *     (getTreatment(key, feature) == treatment) ? true : false;
     * </pre>
     *
     * This method never throws exceptions.
     * Instead of throwing  exceptions, it returns false.
     *
     * @param $key
     * @param $featureName
     * @param $treatment
     * @return bool
     */
    public function isTreatment($key, $featureName, $treatment)
    {
        try {

            $calculatedTreatment = $this->getTreatment($key, $featureName);

            if ($calculatedTreatment !== TreatmentEnum::CONTROL) {
                if ($treatment == $calculatedTreatment) {
                    return true;
                }
            }

        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            SplitApp::logger()->critical("SDK Client on isTreatment is critical");
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
            // @codeCoverageIgnoreEnd
        }

        return false;
    }
}
