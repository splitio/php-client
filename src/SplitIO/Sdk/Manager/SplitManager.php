<?php
namespace SplitIO\Sdk\Manager;

use SplitIO\Component\Common\Di;
use SplitIO\Grammar\Condition;
use SplitIO\Grammar\Split;
use SplitIO\Split as SplitApp;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Sdk\Validator\InputValidator;

class SplitManager implements SplitManagerInterface
{
    public function splitNames()
    {
        $splitKeys = Di::getCache()->getKeys(SplitCache::getCacheKeySearchPattern());

        if (in_array('SPLITIO.split.till', $splitKeys)) {
            $splitKeys = array_diff($splitKeys, array('SPLITIO.split.till'));
        }

        return array_map('SplitIO\Component\Cache\SplitCache::getSplitNameFromCacheKey', $splitKeys);
    }

    /**
     * @return array
     */
    public function splits()
    {
        $_splits = array();

        $splitKeys = Di::getCache()->getKeys(SplitCache::getCacheKeySearchPattern());

        if (empty($splitKeys)) {
            return $_splits;
        }
        
        $cachedSplits = Di::getCache()->getItems($splitKeys);

        foreach ($cachedSplits as $split) {
            $splitView = $this->parseSplitView($split);
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
        $featureName = InputValidator::validateSplitFeatureName($featureName);
        if (is_null($featureName)) {
            return null;
        }

        $splitCacheKey = SplitCache::getCacheKeyForSplit($featureName);
        $splitCachedItem = SplitApp::cache()->getItem($splitCacheKey);

        if ($splitCachedItem->isHit()) {
            return $this->parseSplitView($splitCachedItem->get());
        }

        return null;
    }

    /**
     * @param $splitRepresentation
     * @return SplitView
     */
    private function parseSplitView($splitRepresentation)
    {
        if (empty($splitRepresentation)) {
            return null;
        }

        $split = new Split(json_decode($splitRepresentation, true));

        return new SplitView(
            $split->getName(),
            $split->getTrafficTypeName(),
            $split->killed(),
            $split->getTreatments(),
            $split->getChangeNumber()
        );
    }
}
