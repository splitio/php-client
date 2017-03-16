<?php
namespace SplitIO\Test\Suite\Engine;

use SplitIO\Component\Initialization\LoggerTrait;
use SplitIO\Component\Log\LogLevelEnum;
use SplitIO\Engine\Splitter;
use SplitIO\Grammar\Condition\Partition;
use SplitIO\Engine\HashAlgorithmEnum;

class SplitterTest extends \PHPUnit_Framework_TestCase
{

    public function testDiLog()
    {
        LoggerTrait::addLogger('stdout', LogLevelEnum::ERROR);

        $this->assertTrue(true);
    }

    /**
     * @depends testDiLog
     */
    public function testWorks()
    {
        $partitions = array();
        for ($i = 1; $i <= 100; $i++) {
            $partitions[$i] = new Partition(array('treatment' => "$i", 'size' => 1));
        }

        $treatments = array();
        for ($i = 0; $i < 100; $i++) {
            $treatments[$i] = 0;
        }

        $n = 100000;
        $p = 0.01;

        for ($i = 0; $i < $n; $i++) {
            $key = uniqid('', true);
            $treatment = Splitter::getTreatment($key, 32126754, $partitions, HashAlgorithmEnum::LEGACY);
            $treatments[(int)$treatment - 1]++;
        }

        $mean = $n * $p;
        $stddev = sqrt($mean * (1 - $p));

        $min = (int)($mean - 4 * $stddev);
        $max = (int)($mean + 4 * $stddev);

        $range = range($min, $max);

        for ($i = 0; $i < count($treatments); $i++) {
            $message = "Value: " . $treatments[$i] . " is out of range " . print_r($range, true);
            $this->assertTrue(in_array($treatments[$i], $range), $message);
        }
    }

    /**
     * @depends testDiLog
     */
    public function testSplitterErrorPartions()
    {
        $partition = new Partition(array('treatment' => "on", 'size' => -1));

        $this->assertNull(
            Splitter::getTreatment('someValidKey', 123123545, array($partition), HashAlgorithmEnum::LEGACY)
        );
    }
}
