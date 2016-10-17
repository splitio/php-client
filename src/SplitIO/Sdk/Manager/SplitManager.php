<?php
namespace SplitIO\Sdk\Manager;

use SplitIO\Component\Common\Di;
use SplitIO\Grammar\Condition;
use SplitIO\Grammar\Split;
use SplitIO\Split as SplitApp;
use SplitIO\Component\Cache\SplitCache;

class SplitManager implements SplitManagerInterface
{

    /**
     * @return array
     */
    public function splits()
    {
        $_splits = array();

        $splitKeys = Di::getCache()->getKeys(SplitCache::getCacheKeySearchPattern());

        foreach ($splitKeys as $key) {
            $splitView = $this->getSplitByCacheKey($key);
            if ($splitView != null) {
                $_splits[] = $splitView;
            }
        }

        return $_splits;
    }

    /**
     * @param $featureName
     * @return null|SplitView
     */
    public function split($featureName)
    {
        return $this->getSplitByCacheKey(SplitCache::getCacheKeyForSplit($featureName));
    }

    /**
     * @param $splitCacheKey
     * @return null|SplitView
     */
    private function getSplitByCacheKey($splitCacheKey)
    {
        $splitCachedItem = SplitApp::cache()->getItem($splitCacheKey);

        if ($splitCachedItem->isHit()) {
            $splitRepresentation = $splitCachedItem->get();
            $split = new Split(json_decode($splitRepresentation, true));

            return new SplitView(
                $split->getName(),
                $split->getTrafficTypeName(),
                $split->killed(),
                $split->getTreatments(),
                $split->getChangeNumber()
            );
        }

        return null;
    }
}
