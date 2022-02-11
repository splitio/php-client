<?php
namespace SplitIO\Sdk\Manager;

use \stdClass;
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
        try {
            $cache = new SplitCache();
            return $cache->getSplitNames();
        } catch (\Exception $e) {
            SplitApp::logger()->critical('splitNames method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            return [];
        }
    }

    /**
     * @return array
     */
    public function splits()
    {
        try {
            $cache = new SplitCache();
            $rawSplits = $cache->getAllSplits();
            return array_map('self::parseSplitView', $rawSplits);
        } catch (\Exception $e) {
            SplitApp::logger()->critical('splits method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            return [];
        }
    }

    /**
     * @param $featureName
     * @return null|SplitView
     */
    public function split($featureName)
    {
        try {
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
        } catch (\Exception $e) {
            SplitApp::logger()->critical('split method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            return null;
        }
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
            $configs
        );
    }
}
