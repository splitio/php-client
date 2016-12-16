<?php
namespace SplitIO;

use SplitIO\Grammar\Split as SplitGrammar;
use SplitIO\Grammar\Condition;
use SplitIO\Engine\Splitter;

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
        $conditions = $split->getConditions();

        $result = array(
            self::EVALUATION_RESULT_TREATMENT => null,
            self::EVALUATION_RESULT_LABEL => null
        );

        foreach ($conditions as $condition) {
            if ($condition->match($matchingKey, $attributes)) {
                $result[self::EVALUATION_RESULT_TREATMENT] = Splitter::getTreatment(
                    $bucketingKey,
                    $split->getSeed(),
                    $condition->getPartitions()
                );

                $result[self::EVALUATION_RESULT_LABEL] = $condition->getLabel();
            }
        }

        return $result;
    }
}
