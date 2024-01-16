<?php
namespace SplitIO\Sdk\Manager;

use \stdClass;
use SplitIO\Grammar\Split;
use SplitIO\Split as SplitApp;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Sdk\Validator\InputValidator;

class SplitManager implements SplitManagerInterface
{
    public function splitNames()
    {
        $cache = new SplitCache();
        return $cache->getSplitNames();
    }

    /**
     * @return array
     */
    public function splits()
    {
        $cache = new SplitCache();
        $rawSplits = $cache->getAllSplits();
        return array_map('self::parseSplitView', $rawSplits);
    }

    /**
     * @param $featureName
     * @return null|SplitView
     */
    public function split($featureName)
    {
        $featureName = InputValidator::validateFeatureName($featureName, 'split');
        if (is_null($featureName)) {
            return null;
        }

        $cache = new SplitCache();
        $raw = $cache->getSplit($featureName);
        if (is_null($raw)) {
            SplitApp::logger()->warning("split: you passed " . $featureName
            . " that does not exist in this environment, please double check what Splits exist"
            . " in the web console.");
            return null;
        }
        return self::parseSplitView($raw);
    }

    /**
     * @param $splitRepresentation
     * @return SplitView
     */
    private static function parseSplitView($splitRepresentation)
    {
        if (empty($splitRepresentation)) {
            return null;
        }

        $split = new Split(json_decode($splitRepresentation, true));
        $configs = !is_null($split->getConfigurations()) ? $split->getConfigurations() : new StdClass;

        return new SplitView(
            $split->getName(),
            $split->getTrafficTypeName(),
            $split->killed(),
            $split->getTreatments(),
            $split->getChangeNumber(),
            $configs,
            $split->getDefaultTratment(),
            $split->getSets()
        );
    }
}
