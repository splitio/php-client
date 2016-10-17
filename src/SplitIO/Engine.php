<?php
namespace SplitIO;

use SplitIO\Grammar\Split as SplitGrammar;
use SplitIO\Grammar\Condition;
use SplitIO\Engine\Splitter;

class Engine
{
    /**
     * @param string $key
     * @param \SplitIO\Grammar\Split $split
     * @return null|string
     */
    public static function getTreatment($matchingKey, $bucketingKey, SplitGrammar $split, array $attributes = null)
    {
        $conditions = $split->getConditions();

        foreach ($conditions as $condition) {
            if ($condition->match($matchingKey, $attributes)) {
                return Splitter::getTreatment($bucketingKey, $split->getSeed(), $condition->getPartitions());
            }
        }

        return null;
    }
}
