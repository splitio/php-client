<?php
namespace SplitIO\Sdk;

use SplitIO\Cache\SplitCache;
use SplitIO\Common\Di;
use SplitIO\Engine;
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
}
