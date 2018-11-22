<?php
namespace SplitIO;

use SplitIO\Grammar\Split as SplitGrammar;
use SplitIO\Grammar\Condition;
use SplitIO\Engine\Splitter;
use SplitIO\Grammar\Condition\ConditionTypeEnum;
use SplitIO\Sdk\Impressions\ImpressionLabel;
use SplitIO\Component\Common\Di;

class Engine
{
    const EVALUATION_RESULT_TREATMENT = 'treatment';

    const EVALUATION_RESULT_LABEL = 'label';

    /**
     * @param $matchingKey
     * @param $bucketingKey
     * @param SplitGrammar $split
     * @param array|null $attributes
     * @return array
     */
    public static function getTreatment($matchingKey, $bucketingKey, SplitGrammar $split, array $attributes = null)
    {
        if ($bucketingKey === null) {
            $bucketingKey = $matchingKey;
        }

        $conditions = $split->getConditions();

        $result = array(
            self::EVALUATION_RESULT_TREATMENT => null,
            self::EVALUATION_RESULT_LABEL => null
        );

        $inRollOut = false;
        foreach ($conditions as $condition) {
            if (!$inRollOut  && $condition->getConditionType() == ConditionTypeEnum::ROLLOUT) {
                if ($split->getTrafficAllocation() < 100) {
                    $bucket = Di::get('splitter')->getBucket(
                        $split->getAlgo(),
                        $bucketingKey,
                        $split->getTrafficAllocationSeed()
                    );
                    if ($bucket > $split->getTrafficAllocation()) {
                        $result[self::EVALUATION_RESULT_LABEL] = ImpressionLabel::NOT_IN_SPLIT;
                        $result[self::EVALUATION_RESULT_TREATMENT] = $split->getDefaultTratment();
                        return $result;
                    }
                    $inRollOut = true;
                }
            }
            if ($condition->match($matchingKey, $attributes, $bucketingKey)) {
                $result[self::EVALUATION_RESULT_TREATMENT] = Di::get('splitter')->getTreatment(
                    $bucketingKey,
                    $split->getSeed(),
                    $condition->getPartitions(),
                    $split->getAlgo()
                );

                $result[self::EVALUATION_RESULT_LABEL] = $condition->getLabel();
                //Return the first condition that match.
                return $result;
            }
        }
        return $result;
    }
}
