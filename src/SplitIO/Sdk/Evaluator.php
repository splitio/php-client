<?php

namespace SplitIO\Sdk;

use SplitIO\Component\Cache\SplitCache;
use SplitIO\Engine;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Grammar\Split;
use SplitIO\Metrics;
use SplitIO\Sdk\Impressions\ImpressionLabel;
use SplitIO\Split as SplitApp;

class Evaluator
{
    private $splitCache = null;

    public function __construct()
    {
        $this->splitCache = new SplitCache();
    }

    private function fetchSplit($featureName)
    {
        $splitCachedItem = $this->splitCache->getSplit($featureName);
        if (is_null($splitCachedItem)) {
            return null;
        }
        SplitApp::logger()->info("$featureName is present on cache");
        $split = new Split(json_decode($splitCachedItem, true));
        return $split;
    }

    private function fetchSplits($featureNames)
    {
        $splitCachedItems = $this->splitCache->getSplits($featureNames);
        $toReturn = array();
        foreach ($splitCachedItems as $splitName => $rawSplit) {
            if (is_null($rawSplit)) {
                $toReturn[$splitName] = null;
                continue;
            }
            $toReturn[$splitName] = new Split(json_decode($rawSplit, true));
        }
        return $toReturn;
    }

    private function fetchFeatureFlagNamesByFlagSets($flagSets)
    {
        $namesByFlagSets = $this->splitCache->getNamesByFlagSets($flagSets);
        $toReturn = array();

        foreach ($namesByFlagSets as $flagSet => $flagNames) {
            if (empty($flagNames)) {
                SplitApp::logger()->warning("you passed $flagSet Flag Set that does not contain" .
                'cached feature flag names, please double check what Flag Sets are in use in the Split user interface.');
                continue;
            }

            array_push($toReturn, ...$flagNames);
        }

        return array_values(array_unique($toReturn));
    }

    public function evaluateFeature($matchingKey, $bucketingKey, $featureName, array $attributes = null)
    {
        $timeStart = Metrics::startMeasuringLatency();
        $split = $this->fetchSplit($featureName);
        $toReturn = $this->evalTreatment($matchingKey, $bucketingKey, $split, $attributes);
        $toReturn['latency'] = Metrics::calculateLatency($timeStart);
        return $toReturn;
    }

    public function evaluateFeatures($matchingKey, $bucketingKey, array $featureNames, array $attributes = null)
    {
        $toReturn = array(
            'evaluations' => array(),
            'latency' => 0
        );
        $timeStart = Metrics::startMeasuringLatency();
        foreach ($this->fetchSplits($featureNames) as $name => $split) {
            $toReturn['evaluations'][$name] = $this->evalTreatment($matchingKey, $bucketingKey, $split, $attributes);
        }
        $toReturn['latency'] = Metrics::calculateLatency($timeStart);
        return $toReturn;
    }

    public function evaluateFeaturesByFlagSets($matchingKey, $bucketingKey, array $flagSets, array $attributes = null)
    {
        $timeStart = Metrics::startMeasuringLatency();
        $featureFlagNames = $this->fetchFeatureFlagNamesByFlagSets($flagSets);
        $toReturn = $this->evaluateFeatures($matchingKey, $bucketingKey, $featureFlagNames, $attributes);
        $toReturn['latency'] = Metrics::calculateLatency($timeStart);
        return $toReturn;
    }

    private function evalTreatment($key, $bucketingKey, $split, array $attributes = null)
    {
        $result = array(
            'treatment' => TreatmentEnum::CONTROL,
            'impression' => array(
                'label' => null,
                'changeNumber' => null,
            ),
            'config' => null
        );

        if (is_null($split)) {
            $result['impression']['label'] = ImpressionLabel::SPLIT_NOT_FOUND;
            return $result;
        }
        try {
            $configs = $split->getConfigurations();
            $result['impression']['changeNumber'] = $split->getChangeNumber();
            if ($split->killed()) {
                $defaultTreatment = $split->getDefaultTratment();
                $result['treatment'] = $defaultTreatment;
                $result['impression']['label'] = ImpressionLabel::KILLED;
                if (!is_null($configs) && isset($configs[$defaultTreatment])) {
                    $result['config'] = $configs[$defaultTreatment];
                }
                return $result;
            }

            $evaluationResult = Engine::getTreatment($key, $bucketingKey, $split, $attributes);
            if (!is_null($evaluationResult[Engine::EVALUATION_RESULT_TREATMENT])) {
                $result['treatment'] = $evaluationResult[Engine::EVALUATION_RESULT_TREATMENT];
                $result['impression']['label'] = $evaluationResult[Engine::EVALUATION_RESULT_LABEL];
            } else { // If the given key doesn't match on any condition, default treatment is returned
                $result['treatment'] = $split->getDefaultTratment();
                $result['impression']['label'] = ImpressionLabel::NO_CONDITION_MATCHED;
            }

            if (!is_null($configs) && isset($configs[$result['treatment']])) {
                $result['config'] = $configs[$result['treatment']];
            }
            SplitApp::logger()->info("*Treatment for $key in {$split->getName()} is: ".$result['treatment']);
        } catch (\Exception $e) {
            SplitApp::logger()->critical('An exception occurred when evaluating feature: '. $split->getName());
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
            $result['impression']['label'] = ImpressionLabel::EXCEPTION;
        }
        return $result;
    }
}
