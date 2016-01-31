<?php
namespace SplitIO;

use SplitIO\Common\Di;
use SplitIO\Grammar\Split;
use SplitIO\Grammar\Condition;
use SplitIO\Engine\Splitter;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;

class Engine
{

    public static function isOn($userId, Split $split)
    {
        $treatment = self::getTreatment($userId, $split);
        Di::getInstance()->getLogger()->info("*Treatment for $userId in {$split->getName()} is: $treatment");
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