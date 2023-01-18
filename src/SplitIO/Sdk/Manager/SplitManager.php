<?php
namespace SplitIO\Sdk\Manager;

use \stdClass;
use SplitIO\Grammar\Split;
use SplitIO\Split as SplitApp;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Sdk\Validator\InputValidator;

class SplitManager implements SplitManagerInterface
{
    /**
     * @var \SplitIO\Component\Cache\SplitCache
     */
    private $splitCache;

    public function __construct(SplitCache $splitCache)
    {
        $this->splitCache = $splitCache;
    }

    public function splitNames()
    {
        return $this->splitCache->getSplitNames();
    }

    /**
     * @return array
     */
    public function splits()
    {
        $rawSplits = $this->splitCache->getAllSplits();
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

        $raw = $this->splitCache->getSplit($featureName);
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
     * @return null|SplitView
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
