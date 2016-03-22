<?php
namespace SplitIO\Engine;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Partition;

class Splitter
{
    /**
     * @param string $key
     * @param long $seed
     * @param array $partitions
     * @return null|string
     */
    public static function getTreatment($key, $seed, $partitions)
    {
        SplitApp::logger()->info("Splitter evaluating partitions");
        SplitApp::logger()->info("UserID: ".$key);
        SplitApp::logger()->info("Seed: ".$seed);
        SplitApp::logger()->info("Partitions: ".print_r($partitions, true));

        $bucket = abs(\SplitIO\hash($key, $seed) % 100) + 1;

        SplitApp::logger()->info("Butcket: ".$bucket);

        $accumulatedSize = 0;
        foreach ($partitions as $partition) {
            if ($partition instanceof Partition) {
                $accumulatedSize += $partition->getSize();
                if ($bucket <= $accumulatedSize) {
                    return $partition->getTreatment();
                }
            }
        }

        return null;
    }
}
