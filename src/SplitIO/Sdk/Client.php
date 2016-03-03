<?php
namespace SplitIO\Sdk;

use SplitIO\Cache\SplitCache;
use SplitIO\Common\Di;
use SplitIO\Engine;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Grammar\Split;
use SplitIO\TreatmentImpression;

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
        $splitCachedItem = Di::getInstance()->getCache()->getItem($splitCacheKey);

        if ($splitCachedItem->isHit()) {
            Di::getInstance()->getLogger()->info("$featureName is present on cache");
            $splitRepresentation = $splitCachedItem->get();

            $split = new Split(json_decode($splitRepresentation, true));

            if ($split->killed()) {
                return $split->getDefaultTratment();
            }

            $treatment = Engine::getTreatment($key, $split);

            TreatmentImpression::log($key, $featureName, $treatment);

            Di::getInstance()->getLogger()->info("*Treatment for $key in {$split->getName()} is: $treatment");

            if ($treatment !== null) {
                return $treatment;
            }

            return $split->getDefaultTratment();
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
                } else {
                    return false;
                }
            }

        } catch (\Exception $e) {
            Di::getInstance()->getLogger()->critical("SDK Client on isTreatment is critical");
            Di::getInstance()->getLogger()->critical($e->getMessage());
            Di::getInstance()->getLogger()->critical($e->getTraceAsString());
        }

        return false;
    }
}
