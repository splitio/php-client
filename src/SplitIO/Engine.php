<?php
namespace SplitIO;

use SplitIO\Grammar\Split;
use SplitIO\Grammar\Condition;
use SplitIO\Engine\Splitter;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;

class Engine
{
    //private $splitList = [];

    public static function isOn($userId, Split $split)
    {
        $treatment = self::getTreatment($userId, $split);
        if ($treatment != TreatmentEnum::OFF && $treatment != TreatmentEnum::CONTROL) {
            return true;
        }

        return false;
    }

    public static function getTreatment($userId, Split $split)
    {
        $conditions = $split->getConditions();

        foreach ($conditions as $condition) {
            if ($condition->match($userId)) {
                return Splitter::getTreatment($userId, $split->getSeed(), $condition->getPartitions());
            }
        }

        return TreatmentEnum::CONTROL;
    }
}