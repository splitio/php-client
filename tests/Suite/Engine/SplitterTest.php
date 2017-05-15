<?php
namespace SplitIO\Test\Suite\Engine;

use SplitIO\Component\Initialization\LoggerTrait;
use SplitIO\Component\Log\LogLevelEnum;
use SplitIO\Engine\Splitter;
use SplitIO\Grammar\Condition\Partition;
use SplitIO\Engine\Hash\HashAlgorithmEnum;
use SplitIO\Grammar\Split;
use SplitIO\Engine;

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

    public function testTrafficAllocation()
    {
        $rawSplit = array(
            'name' => 'test1',
            'algo' => 1,
            'killed' => false,
            'status' => 'ACTIVE',
            'defaultTreatment' => 'default',
            'seed' => -1222652054,
            'orgId' => null,
            'environment' => null,
            'trafficTypeId' => null,
            'trafficTypeName' => null,
            'conditions' => array(
                array(
                    'conditionType' => 'WHITELIST',
                    'matcherGroup' => array(
                        'combiner' => 'AND',
                        'matchers' => array(
                            array(
                                'matcherType' => 'ALL_KEYS',
                                'negate' => false,
                                'userDefinedSegmentMatcherData' => null,
                                'whitelistMatcherData' => null
                            )
                        )
                    ),
                    'partitions' => array(
                        array(
                            'treatment' => 'on',
                            'size' => 100
                        )
                    ),
                    'label' => 'in segment all'
                )
            )
        );
    
        // Test that conditionType = WHITELIST works normally
        $split1 = new Split($rawSplit);
        $treatment1 = Engine::getTreatment('testKey', null, $split1);
        $this->assertEquals('on', $treatment1[Engine::EVALUATION_RESULT_TREATMENT]);

        // Test that conditionType = ROLLOUT w/o trafficAllocation behaves like WHITELIST
        $rawSplit['conditions'][0]['conditionType'] = 'ROLLOUT';
        $split2 = new Split($rawSplit);
        $treatment2 = Engine::getTreatment('testKey', null, $split2);
        $this->assertEquals('on', $treatment2[Engine::EVALUATION_RESULT_TREATMENT]);

        // Set a low trafficAllocation to force the bucket outside it.
        $rawSplit['trafficAllocation'] = 1;
        $rawSplit['trafficAllocationSeed'] = -1;
        $split3 = new Split($rawSplit);
        $treatment3 = Engine::getTreatment('testKey', null, $split3);
        $this->assertEquals('default', $treatment3[Engine::EVALUATION_RESULT_TREATMENT]);
    }
}
