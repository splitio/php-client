<?php
namespace SplitIO\Sdk;

use SplitIO\Cache\SplitCache;
use SplitIO\Common\Di;
use SplitIO\Engine;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Grammar\Split;

class Client
{

    public function isOn($key, $featureName)
    {
        $splitCacheKey = SplitCache::getCacheKeyForSplit($featureName);
        $splitCachedItem = Di::getInstance()->getCache()->getItem($splitCacheKey);

        if ($splitCachedItem->isHit()) {
            Di::getInstance()->getLogger()->info("$featureName is present on cache");
            $splitRepresentation = $splitCachedItem->get();

            $split = new Split(json_decode($splitRepresentation, true));

            return Engine::isOn($key, $split);
        }

        Di::getInstance()->getLogger()->info("Returning FALSE - $featureName is not on cache");
        return false;
    }

    private function getTratmentFromSplit($key, $featureName)
    {
        $splitCacheKey = SplitCache::getCacheKeyForSplit($featureName);
        $splitCachedItem = Di::getInstance()->getCache()->getItem($splitCacheKey);

        if ($splitCachedItem->isHit()) {
            Di::getInstance()->getLogger()->info("$featureName is present on cache");
            $splitRepresentation = $splitCachedItem->get();

            $split = new Split(json_decode($splitRepresentation, true));

            return Engine::getTreatment($key, $split);
        }

        return false;
    }

    public function isTreatment($key, $featureName, $treatment)
    {
        $calculatedTreatment = $this->getTratmentFromSplit($key, $featureName);

        if ($calculatedTreatment !== false) {
            if ($treatment == $calculatedTreatment) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    public function getTreatment($key, $featureName, $defaultTreatment)
    {
        $calculatedTreatment = $this->getTratmentFromSplit($key, $featureName);

        if ($calculatedTreatment !== false) {
            if ($calculatedTreatment != TreatmentEnum::CONTROL) {
                return $calculatedTreatment;
            } else {
                return $defaultTreatment;
            }
        }

        return $defaultTreatment;
    }
}
