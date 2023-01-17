<?php
namespace SplitIO\Test\Suite\TrafficAllocation;

use SplitIO\Component\Initialization\LoggerFactory;
use SplitIO\Component\Common\ServiceProvider;
use SplitIO\Grammar\Split;
use SplitIO\Component\Common\Context;

class TrafficAllocationTest extends \PHPUnit\Framework\TestCase
{
    /**
    * @runInSeparateProcess
    * @preserveGlobalState disabled
    */
	public function testTrafficAllocation()
    {
		$mock = \Mockery::mock('alias:\SplitIO\Engine\Splitter');
		$mock->shouldReceive([
			'getBucket' => 1,
			'getTreatment' => 'on',
		]);

        $logger = LoggerFactory::setupLogger(array('adapter' => 'stdout', 'level' => 'error'));
        Context::setLogger($logger);

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
	    
		$rawSplit['trafficAllocation'] = 1;
		$rawSplit['trafficAllocationSeed'] = -1;
		$split4 = new Split($rawSplit);
		$treatment4 = \SplitIO\Engine::getTreatment('testKey', null, $split4);
		$this->assertEquals('on', $treatment4[\SplitIO\Engine::EVALUATION_RESULT_TREATMENT]);

		$rawSplit['trafficAllocation'] = 1;
		$rawSplit['trafficAllocationSeed'] = -1;
		$split4 = new Split($rawSplit);
		$treatment4 = \SplitIO\Engine::getTreatment('testKey', null, $split4);
		$this->assertEquals('on', $treatment4[\SplitIO\Engine::EVALUATION_RESULT_TREATMENT]);
	}
};