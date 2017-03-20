<?php
namespace SplitIO\Test\Suite\Engine;

use SplitIO\Engine\Hash\LegacyHash;
use SplitIO\Engine\Hash\Murmur3Hash;

class HashTest extends \PHPUnit_Framework_TestCase
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
}
