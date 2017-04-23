<?php
namespace SplitIO;

use SplitIO\Grammar\Split as SplitGrammar;
use SplitIO\Grammar\Condition;
use SplitIO\Engine\Splitter;
use SplitIO\Grammar\Condition\ConditionTypeEnum;

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
                    $bucket = Splitter::getBucket(
                        $split->getAlgo(),
                        $split->getKey(),
                        $split->getTrafficAllocationSeed()
                    );
                    if ($bucket >= $split->getTrafficAllocation()) {
                        return $split->getDefaultTratment();
                    }
                    $inRollOut = true;
                }
            }
            if ($condition->match($matchingKey, $attributes)) {
                $result[self::EVALUATION_RESULT_TREATMENT] = Splitter::getTreatment(
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
