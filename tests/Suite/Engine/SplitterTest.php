<?php
namespace SplitIO\Test\Suite\Engine;

use SplitIO\Common\Di;
use SplitIO\Engine\Splitter;
use SplitIO\Grammar\Condition\Partition;
use SplitIO\Log\Handler\Stdout;
use SplitIO\Log\Logger;
use SplitIO\Log\LogLevelEnum;

class SplitterTest extends \PHPUnit_Framework_TestCase
{
    public function testDiLog()
    {
        $logAdapter = new Stdout();

        $logger = new Logger($logAdapter, LogLevelEnum::ERROR);

        Di::getInstance()->setLogger($logger);

        $this->assertTrue(true);
    }

    /**
     * @depends testDiLog
     */
    public function testWorks()
    {
        $partitions = [];
        for ($i = 1; $i <= 100; $i++) {
            $partitions[$i] = new Partition(['treatment' => "$i", 'size' => 1]);
        }

        //$treatments = range(1, 100);
        $treatments = [];
        for ($i = 0; $i < 100; $i++) {
            $treatments[$i] = 0;
        }

        $n = 100000;
        $p = 0.01;

        for ($i = 0; $i < $n; $i++) {
            $key = \SplitIO\uuid();
            $treatment = Splitter::getTreatment($key, 123, $partitions);
            $treatments[(int)$treatment - 1]++;
        }


        //var_dump($treatments); exit;

        $mean = $n * $p;
        $stddev = sqrt($mean * (1 - $p));

        $min = (int)($mean - 4 * $stddev);
        $max = (int)($mean + 4 * $stddev);

        //echo "MEAN: $mean   STDDEV: $stddev   MIN: $min   MAX: $max"; exit;

        $range = range($min, $max);

        for ($i = 0; $i < count($treatments); $i++) {
            $this->assertTrue( in_array($treatments[$i], $range), "Value: " . $treatments[$i] . " is out of range " . print_r($range,true));
        }
    }
}