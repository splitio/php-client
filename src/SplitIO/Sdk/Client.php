<?php
namespace SplitIO\Sdk;

use SplitIO\Common\Di;
use SplitIO\Engine;
use SplitIO\Grammar\Split;

class Client
{

    public function isOn($key, $featureName)
    {
        $splitCachedItem = Di::getInstance()->getCache()->getItem(\SplitIO\getCacheKeyForSplit($featureName));

        if ($splitCachedItem->isHit()) {
            Di::getInstance()->getLogger()->info("$featureName is present on cache");
            $split = unserialize($splitCachedItem->get());

            if ($split instanceof Split) {
                return Engine::isOn($key, $split);
            }
        }

        Di::getInstance()->getLogger()->info("Returning FALSE - $featureName is not on cache");
        return false;
    }
}