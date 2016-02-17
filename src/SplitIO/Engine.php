<?php
namespace SplitIO;

use SplitIO\Common\Di;
use SplitIO\Grammar\Split;
use SplitIO\Grammar\Condition;
use SplitIO\Engine\Splitter;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;

class Engine
{

    public static function isOn($key, Split $split)
    {
        $treatment = self::getTreatment($key, $split);

        Di::getInstance()->getLogger()->info("*Treatment for $key in {$split->getName()} is ---> ".$treatment);

        if ($treatment !== null) {
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @param Split $split
     * @return null|string
     */
    public static function getTreatment($key, Split $split)
    {
        $conditions = $split->getConditions();

        foreach ($conditions as $condition) {
            if ($condition->match($key)) {
                return Splitter::getTreatment($key, $split->getSeed(), $condition->getPartitions());
            }
        }

        return null;
    }
}
