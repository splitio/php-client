<?php
namespace SplitIO\Test\Suite\Metrics;

use SplitIO\Grammar\Semver\Semver;
use SplitIO\Grammar\Semver\SemverComparer;

class ComparerTests extends \PHPUnit\Framework\TestCase
{
    public function testGreaterThanOrEqualTo()
    {
        $handle = fopen(__DIR__."/../../files/valid-semantic-versions.csv", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $_line = explode(',', $line);

                $v1 = str_replace("\n","",$_line[0]);
                $v2 = str_replace("\n","",$_line[1]);

                $semver1 = Semver::build($v1);
                $semver2 = Semver::build($v2);

                $this->assertTrue(SemverComparer::do($semver1, $semver2) >= 0);
                $this->assertTrue(SemverComparer::do($semver1, $semver1) >= 0);
                $this->assertTrue(SemverComparer::do($semver2, $semver2) >= 0);
                $this->assertFalse(SemverComparer::do($semver2, $semver1) >= 0);
            }

            fclose($handle);
        } else {
            $this->assertTrue(false, "Sample Data not found");
        }
    }

    public function testLessThanOrEqualTo()
    {
        $handle = fopen(__DIR__."/../../files/valid-semantic-versions.csv", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $_line = explode(',', $line);

                $v1 = str_replace("\n","",$_line[0]);
                $v2 = str_replace("\n","",$_line[1]);

                $semver1 = Semver::build($v1);
                $semver2 = Semver::build($v2);

                $this->assertFalse(SemverComparer::do($semver1, $semver2) <= 0);
                $this->assertTrue(SemverComparer::do($semver2, $semver1) <= 0);
                $this->assertTrue(SemverComparer::do($semver1, $semver1) <= 0);
                $this->assertTrue(SemverComparer::do($semver2, $semver2) <= 0);
            }

            fclose($handle);
        } else {
            $this->assertTrue(false, "Sample Data not found");
        }
    }

    public function testEquals()
    {
        $handle = fopen(__DIR__."/../../files/equal-to-semver.csv", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $_line = explode(',', $line);

                $c1 = str_replace("\n","",$_line[0]);
                $c2 = str_replace("\n","",$_line[1]);
                $c3 = str_replace("\n","",$_line[2]);

                $semver1 = Semver::build($c1);
                $semver2 = Semver::build($c2);
                $expected = (bool) $c3;

                $this->assertEquals($expected, SemverComparer::equals($semver1, $semver2), $semver1->getVersion() . " - " . $semver2->getVersion() . " | " . $expected);
            }

            fclose($handle);
        } else {
            $this->assertTrue(false, "Sample Data not found");
        }
    }

    public function testBetween()
    {
        $handle = fopen(__DIR__."/../../files/between-semver.csv", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $_line = explode(',', $line);

                $c1 = str_replace("\n","",$_line[0]);
                $c2 = str_replace("\n","",$_line[1]);
                $c3 = str_replace("\n","",$_line[2]);
                $c4 = str_replace("\n","",$_line[3]);

                $semver1 = Semver::build($c1);
                $semver2 = Semver::build($c2);
                $semver3 = Semver::build($c3);
                
                $result = SemverComparer::do($semver2, $semver1) >= 0 && SemverComparer::do($semver2, $semver3) <= 0;

                $this->assertEquals((bool) $c4, $result);
            }

            fclose($handle);
        } else {
            $this->assertTrue(false, "Sample Data not found");
        }
    }
}