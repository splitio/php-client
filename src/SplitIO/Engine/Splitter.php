<?php
namespace SplitIO\Engine;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Partition;
use SplitIO\Engine\HashAlgorithmEnum;

class Splitter
{
    /**
     * @param string $key
     * @param long $seed
     * @param array $partitions
     * @return null|string
     */
    public static function getTreatment($key, $seed, $partitions, $algo)
    {
        $logMsg = "Splitter evaluating partitions ... \n
        Bucketing Key: $key \n
        Seed: $seed \n
        Partitions: ". print_r($partitions, true);

        SplitApp::logger()->debug($logMsg);

        switch ($algo) {
            case HashAlgorithmEnum::MURMUR:
                $hash = \SplitIO\murmurhash3_int($key, $seed);
                break;
            case HashAlgorithmEnum::LEGACY:
            default:
                $hash = \SplitIO\splitHash($key, $seed);
        }

        $bucket = abs($hash  % 100) + 1;
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
