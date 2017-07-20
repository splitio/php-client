<?php
namespace SplitIO\Sdk;

use SplitIO\Exception\InvalidMatcherException;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;
use SplitIO\Split as SplitApp;

class MatcherClient
{
    private $evaluator = null;

    /**
     * @param array $options
     */
    public function __construct(Evaluator $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * Wraps an Evaluator instance and forwards getTreatment calls to it.
     * Does not register impressions nor metrics.
     *
     * @param $key
     * @param $featureName
     * @param $attributes
     * @return string
     */
    public function getTreatment($key, $featureName, array $attributes = null)
    {
        //Getting Matching key and Bucketing key
        if ($key instanceof Key) {
            $matchingKey = $key->getMatchingKey();
            $bucketingKey = $key->getBucketingKey();
        } else {
            $matchingKey = $key;
            $bucketingKey = null;
        }

        try {
            $result = $this->evaluator->evalTreatment($matchingKey, $bucketingKey, $featureName, $attributes);
            return $result['treatment'];
        } catch (InvalidMatcherException $ie) {
            SplitApp::logger()->critical('Exception due an INVALID MATCHER in nested treatment');
        } catch (\Exception $e) {
            SplitApp::logger()->critical('Nest getTreatment method is throwing exception');
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
        }
        return TreatmentEnum::CONTROL;
    }
}
