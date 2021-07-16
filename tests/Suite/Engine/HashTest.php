<?php
namespace SplitIO\Test\Suite\Engine;

use SplitIO\Engine\Hash\HashFactory;
use SplitIO\Engine\Hash\LegacyHash;
use SplitIO\Engine\Hash\Murmur3Hash;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Engine\Hash\HashAlgorithmEnum;
use SplitIO\Grammar\Split;
use SplitIO\Split as SplitApp;

class HashTest extends \PHPUnit\Framework\TestCase
{
    public function testLegacyHashFunction()
    {
        $handle = fopen(__DIR__."/../../files/sample-data.csv", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $_line = explode(',', $line);

                if ($_line[0] == '#seed') {
                    continue;
                }

                $hashfn = new LegacyHash();
                $hash = $hashfn->getHash($_line[1], $_line[0]);
                $bucket = abs($hash % 100) + 1;

                $this->assertEquals((int)$_line[2], (int)$hash, "Hash, Expected: ".$_line[2]." Calculated: ".$hash);
                $this->assertEquals(
                    (int)$_line[3],
                    (int)$bucket,
                    "Bucket, Expected: ".$_line[3]." Calculated: ".$bucket
                );
            }

            fclose($handle);
        } else {
            $this->assertTrue(false, "Sample Data not found");
        }
    }

    public function testMurmur3HashFunction()
    {
        $handles = array(
            fopen(__DIR__."/../../files/murmur3-sample-data-v2.csv", "r"),
            fopen(__DIR__."/../../files/murmur3-sample-data-non-alpha-numeric-v2.csv", "r"),
        );

        foreach ($handles as $handle) {
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $_line = explode(',', $line);

                    if ($_line[0] == '#seed') {
                        continue;
                    }

                    $hashfn = new Murmur3Hash();
                    $hash = $hashfn->getHash($_line[1], $_line[0]);
                    $bucket = abs($hash % 100) + 1;

                    $this->assertEquals((int)$_line[2], (int)$hash, "Hash, Expected: ".$_line[2]." Calculated: ".$hash);
                    $this->assertEquals(
                        (int)$_line[3],
                        (int)$bucket,
                        "Bucket, Expected: ".$_line[3]." Calculated: ".$bucket
                    );
                }
                fclose($handle);
            } else {
                $this->assertTrue(false, "Sample Data not found");
            }
        }
    }

    private function addSplitsInCache()
    {
        $splitChanges = file_get_contents(__DIR__."/../../files/algoSplits.json");
        $this->assertJson($splitChanges);

        $splitCache = new SplitCache();

        $splitChanges = json_decode($splitChanges, true);
        $splits = $splitChanges['splits'];

        foreach ($splits as $split) {
            $splitName = $split['name'];
            $this->assertTrue($splitCache->addSplit($splitName, json_encode($split)));
        }
    }

    public function testAlgoField()
    {
        $options = array();

        $sdkConfig = array(
            'log' => array('adapter' => LOG_ADAPTER),
            'cache' => array('adapter' => 'redis', 'client' => new \RedisMock),
            'static_cache' => array('class' => \VoidStaticCache::class)
        );

        //Initializing the SDK instance.
        \SplitIO\Component\Common\Di::set(\SplitIO\Component\Common\Di::KEY_FACTORY_TRACKER, false);
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123457', $sdkConfig);
        $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();

        $cases = array(
            array(  // Split with algo = 1. Should use legacy function
                'key' => 'some_feature_1',
                'algo' => HashAlgorithmEnum::LEGACY,
                'class' => '\SplitIO\Engine\Hash\LegacyHash'
            ),
            array(  // Split with algo = 2. Should use murmur function
                'key' => 'some_feature_2',
                'algo' => HashAlgorithmEnum::MURMUR,
                'class' => '\SplitIO\Engine\Hash\Murmur3Hash'
            ),
            array(  // Split with algo = null. Should use legacy function
                'key' => 'some_feature_3',
                'algo' => HashAlgorithmEnum::LEGACY,
                'class' => '\SplitIO\Engine\Hash\LegacyHash'
            ),
            array(  // Split with no algo field. SHould use legacy function
                'key' => 'some_feature_4',
                'algo' => HashAlgorithmEnum::LEGACY,
                'class' => '\SplitIO\Engine\Hash\LegacyHash'
            ),
        );

        $splitCache = new SplitCache();
        foreach ($cases as $case) {
            $split = new Split(json_decode($splitCache->getSplit($case['key']), true));
            $this->assertEquals($split->getAlgo(), $case['algo']);
            $hasher = HashFactory::getHashAlgorithm($split->getAlgo());
            $this->assertInstanceof($case['class'], $hasher);
        }
    }
}
