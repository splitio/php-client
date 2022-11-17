<?php
namespace SplitIO\Test\Suite\Engine;

use SplitIO\Component\Initialization\LoggerFactory;
use SplitIO\Component\Common\ServiceProvider;
use SplitIO\Component\Log\LogLevelEnum;
use SplitIO\Grammar\Condition\Partition;
use SplitIO\Engine\Hash\HashAlgorithmEnum;
use SplitIO\Grammar\Split;
use SplitIO\Engine;
use SplitIO\Component\Common\Di;
use SplitIO\Engine\Splitter;
use \Mockery;

class SplitterTrafficAllocationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testTrafficAllocation()
    {
        $logger = LoggerFactory::setupLogger(array('adapter' => 'stdout', 'level' => 'error'));
        ServiceProvider::registerLogger($logger);

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

        // $mock = \Mockery::mock('alias:\SplitIO\Engine\Splitter');
        // $mock = \Mockery::mock('alias:\SplitIO\Engine\Splitter');
        $mock = \Mockery::mock('alias:' . Splitter::CLASS);
        //$mock = Mockery::namedMock(Splitter::class,SplitterStub::class);
        $mock
            ->shouldReceive('getBucket')
            ->andReturn(1);
    
        $rawSplit['trafficAllocation'] = 1;
        $rawSplit['trafficAllocationSeed'] = -1;
        $split4 = new Split($rawSplit);
        $treatment4 = Engine::getTreatment('testKey', null, $split4);
        $this->assertEquals('on', $treatment4[Engine::EVALUATION_RESULT_TREATMENT]);

        $rawSplit['trafficAllocation'] = 1;
        $rawSplit['trafficAllocationSeed'] = -1;
        $split4 = new Split($rawSplit);
        $treatment4 = Engine::getTreatment('testKey', null, $split4);
        $this->assertEquals('on', $treatment4[Engine::EVALUATION_RESULT_TREATMENT]);

        /*
        // Set bucket to 1 with low traffic allocation.
        $splitterMocked = $this
            ->getMockBuilder('\SplitIO\Engine\Splitter')
            ->disableOriginalConstructor()
            ->setMethods(array('getBucket'))
            ->getMock();

        $splitterMocked->method('getBucket')->willReturn(1);

        Di::set('splitter', $splitterMocked);

        $rawSplit['trafficAllocation'] = 1;
        $rawSplit['trafficAllocationSeed'] = -1;
        $split4 = new Split($rawSplit);
        $treatment4 = Engine::getTreatment('testKey', null, $split4);
        $this->assertEquals('on', $treatment4[Engine::EVALUATION_RESULT_TREATMENT]);

        // Set bucket to 100 with high traffic allocation.
        $splitterMocked2 = $this
            ->getMockBuilder('\SplitIO\Engine\Splitter')
            ->disableOriginalConstructor()
            ->setMethods(array('getBucket'))
            ->getMock();

        $splitterMocked2->method('getBucket')->willReturn(100);

        Di::set('splitter', $splitterMocked2);

        $rawSplit['trafficAllocation'] = 99;
        $rawSplit['trafficAllocationSeed'] = -1;
        $split5 = new Split($rawSplit);
        $treatment5 = Engine::getTreatment('testKey', null, $split5);
        $this->assertEquals('default', $treatment5[Engine::EVALUATION_RESULT_TREATMENT]);
        */
    }

    public static function tearDownAfterClass(): void
    {
        Mockery::close();
    }
}
