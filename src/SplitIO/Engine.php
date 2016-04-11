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
    public static function getTreatment($key, SplitGrammar $split, array $attributes = null)
    {
        $conditions = $split->getConditions();

        foreach ($conditions as $condition) {
            if ($condition->match($key, $attributes)) {
                return Splitter::getTreatment($key, $split->getSeed(), $condition->getPartitions());
            }
        }

        return null;
    }
}
