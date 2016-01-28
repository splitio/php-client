<?php
namespace SplitIO\Engine;


use SplitIO\Common\Di;
use SplitIO\Grammar\Condition\Partition;
use SplitIO\Grammar\Condition\Partition\TreatmentEnum;

class Splitter
{
    public static function getTreatment($userId, $seed, $partitions)
    {
        Di::getInstance()->getLogger()->info("Splitter evaluating partitions");

        Di::getInstance()->getLogger()->info("UserID: ".$userId);
        Di::getInstance()->getLogger()->info("Seed: ".$seed);
        Di::getInstance()->getLogger()->info("Partitions: ".print_r($partitions, true));

        $bucket = (\SplitIO\hash($userId, $seed) % 100) + 1;

        Di::getInstance()->getLogger()->info("Butcket: ".$bucket);

        $accumulatedSize = 0;
        foreach ($partitions as $partition) {
            if ($partition instanceof Partition) {
                $accumulatedSize += $partition->getSize();
                if ($bucket <= $accumulatedSize) {
                    return $partition->getTreatment()->getValue();
                }
            }
        }

        return TreatmentEnum::CONTROL;
    }
}