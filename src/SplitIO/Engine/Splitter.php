<?php
namespace SplitIO\Engine;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Partition;
use SplitIO\Engine\Hash\HashFactory;

class Splitter
{
    /**
     * @param HashAlgorithmEnum $algo
     * @param string $key
     * @param long $seed
     * @return int
     */
    public static function getBucket($algo, $key, $seed)
    {
        $hashFactory = HashFactory::getHashAlgorithm($algo);
        $hash = $hashFactory->getHash($key, $seed);
        return abs($hash  % 100) + 1;
    }


    /**
     * @param string $key
     * @param long $seed
     * @param array $partitions
     * @param HashAlgorithmEnum $algo
     * @return null|string
     */
    public static function getTreatment($key, $seed, $partitions, $algo)
    {
        $logMsg = "Splitter evaluating partitions ... \n
        Bucketing Key: $key \n
        Seed: $seed \n
        Partitions: ". print_r($partitions, true);

        SplitApp::logger()->debug($logMsg);
        
        $bucket = self::getBucket($algo, $key, $seed);
        SplitApp::logger()->info("Bucket: ".$bucket);

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
