<?php
namespace SplitIO;

use SplitIO\Grammar\Split;
use SplitIO\Grammar\Condition;

class Engine
{
    //private $splitList = [];

    public static function getTreatment($userId, Split $split)
    {
        $conditions = $split->getConditions();

        foreach ($conditions as $condition) {
            if ($condition->match($userId)) {
                //$partition = $condition->getPartitions();
                //return Splitter.getTreatment($userId, $split->seed(), $split->partitions());
                return "on";
            }
        }

        return "off";
    }
}