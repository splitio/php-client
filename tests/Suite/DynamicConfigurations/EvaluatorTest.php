<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Test\Suite\Redis\ReflectiveTools;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Sdk\Evaluator;

class EvaluatorTest extends \PHPUnit\Framework\TestCase
{
    private $split1 = <<<EOD
{"trafficTypeName":"user","name":"mysplittest","trafficAllocation":100,
"trafficAllocationSeed":-285565213,"seed":-1992295819,"status":"ACTIVE","killed":false,
"defaultTreatment":"off","changeNumber":1494593336752,"algo":2,"conditions":[{"conditionType"
:"ROLLOUT","matcherGroup":{"combiner":"AND","matchers":[{"keySelector":{"trafficType"
:"user","attribute":null},"matcherType":"ALL_KEYS","negate":false,"userDefinedSegmentMatcherData":null,
"whitelistMatcherData":null,"unaryNumericMatcherData":null,"betweenMatcherData":null,"booleanMatcherData"
:null,"dependencyMatcherData":null,"stringMatcherData":null}]},"partitions":[{"treatment":"on","size":0}
,{"treatment":"off","size":100}],"label":"default rule"}]}
EOD;

    private $split2 = <<<EOD
{"trafficTypeName":"user","name":"mysplittest2","trafficAllocation":100,
"trafficAllocationSeed":1252392550,"seed":971538037,"status":"ACTIVE","killed":false,
"defaultTreatment":"off","changeNumber":1494593352077,"algo":2,"conditions":[{"conditionType"
:"ROLLOUT","matcherGroup":{"combiner":"AND","matchers":[{"keySelector":{"trafficType"
:"user","attribute":null},"matcherType":"ALL_KEYS","negate":false,"userDefinedSegmentMatcherData":
null,"whitelistMatcherData":null,"unaryNumericMatcherData":null,"betweenMatcherData":null,
"booleanMatcherData":null,"dependencyMatcherData":null,"stringMatcherData":null}]},"partitions"
:[{"treatment":"on","size":100},{"treatment":"off","size":0}],"label":"default rule"}
],"configurations":{"on":"{\"color\": \"blue\",\"size\": 13}"}}
EOD;

    private $split3 = <<<EOD
{"trafficTypeName":"user","name":"mysplittest3","trafficAllocation":100,
"trafficAllocationSeed":1252392550,"seed":971538037,"status":"ACTIVE","killed":true,
"defaultTreatment":"killed","changeNumber":1494593352077,"algo":2,"conditions":[{"conditionType"
:"ROLLOUT","matcherGroup":{"combiner":"AND","matchers":[{"keySelector":{"trafficType"
:"user","attribute":null},"matcherType":"ALL_KEYS","negate":false,"userDefinedSegmentMatcherData"
:null,"whitelistMatcherData":null,"unaryNumericMatcherData":null,"betweenMatcherData":null,
"booleanMatcherData":null,"dependencyMatcherData":null,"stringMatcherData":null}]},"partitions"
:[{"treatment":"on","size":100},{"treatment":"off","size":0}],"label":"default rule"}
],"configurations":{"on":"{\"color\": \"blue\",\"size\": 13}"}}
EOD;

    private $split4 = <<<EOD
{"trafficTypeName":"user","name":"mysplittest4","trafficAllocation":100,
"trafficAllocationSeed":1252392550,"seed":971538037,"status":"ACTIVE","killed":true,
"defaultTreatment":"killed","changeNumber":1494593352077,"algo":2,"conditions":[{"conditionType"
:"ROLLOUT","matcherGroup":{"combiner":"AND","matchers":[{"keySelector":{"trafficType"
:"user","attribute":null},"matcherType":"ALL_KEYS","negate":false,"userDefinedSegmentMatcherData"
:null,"whitelistMatcherData":null,"unaryNumericMatcherData":null,"betweenMatcherData":null,
"booleanMatcherData":null,"dependencyMatcherData":null,"stringMatcherData":null}]},"partitions"
:[{"treatment":"on","size":100},{"treatment":"off","size":0}],"label":"default rule"}
],"configurations":{"killed":"{\"color\": \"orange\",\"size\": 13}",
"on":"{\"color\": \"blue\",\"size\": 13}"}}
EOD;

    public function testSplitWithoutConfigurations()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $factory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $redisClient = ReflectiveTools::clientFromFactory($factory);
        $cachePool = ReflectiveTools::cacheFromFactory($factory);

        $redisClient->del('SPLITIO.split.mysplittest');
        $redisClient->set('SPLITIO.split.mysplittest', $this->split1);

        $segmentCache = new SegmentCache($cachePool);
        $splitCache = new SplitCache($cachePool);
        $evaluator = new Evaluator($splitCache, $segmentCache);

        $result = $evaluator->evaluateFeature('test', '', 'mysplittest', null);

        $this->assertEquals('off', $result['treatment']);
        $this->assertEquals(null, $result['config']);

        $redisClient->del('SPLITIO.split.mysplittest');
    }

    public function testSplitWithConfigurations()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $factory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $cachePool = ReflectiveTools::cacheFromFactory($factory);
        $redisClient = ReflectiveTools::clientFromFactory($factory);

        $redisClient->del('SPLITIO.split.mysplittest2');
        $redisClient->set('SPLITIO.split.mysplittest2', $this->split2);

        $segmentCache = new SegmentCache($cachePool);
        $splitCache = new SplitCache($cachePool);
        $evaluator = new Evaluator($splitCache, $segmentCache);
        $result = $evaluator->evaluateFeature('test', '', 'mysplittest2', null);

        $this->assertEquals('on', $result['treatment']);
        $this->assertEquals($result['config'], '{"color": "blue","size": 13}');

        $redisClient->del('SPLITIO.split.mysplittest2');
    }

    public function testSplitWithConfigurationsButKilled()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $factory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $cachePool = ReflectiveTools::cacheFromFactory($factory);
        $redisClient = ReflectiveTools::clientFromFactory($factory);

        $redisClient->del('SPLITIO.split.mysplittest3');
        $redisClient->set('SPLITIO.split.mysplittest3', $this->split3);

        $segmentCache = new SegmentCache($cachePool);
        $splitCache = new SplitCache($cachePool);
        $evaluator = new Evaluator($splitCache, $segmentCache);
        $result = $evaluator->evaluateFeature('test', '', 'mysplittest3', null);

        $this->assertEquals('killed', $result['treatment']);
        $this->assertEquals($result['config'], null);

        $redisClient->del('SPLITIO.split.mysplittest3');
    }

    public function testSplitWithConfigurationsButKilledWithConfigsOnDefault()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $factory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $cachePool = ReflectiveTools::cacheFromFactory($factory);
        $redisClient = ReflectiveTools::clientFromFactory($factory);

        $redisClient->del('SPLITIO.split.mysplittest4');
        $redisClient->set('SPLITIO.split.mysplittest4', $this->split4);

        $segmentCache = new SegmentCache($cachePool);
        $splitCache = new SplitCache($cachePool);
        $evaluator = new Evaluator($splitCache, $segmentCache);
        $result = $evaluator->evaluateFeature('test', '', 'mysplittest4', null);

        $this->assertEquals('killed', $result['treatment']);
        $this->assertEquals($result['config'], '{"color": "orange","size": 13}');

        $redisClient->del('SPLITIO.split.mysplittest4');
    }

    public function testEvaluateFeaturesByFlagSets()
    {
        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $factory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $cachePool = ReflectiveTools::cacheFromFactory($factory);
        $redisClient = ReflectiveTools::clientFromFactory($factory);

        $redisClient->del('SPLITIO.flagSet.set_1');
        $redisClient->del('SPLITIO.flagSet.set_2');
        $redisClient->del('SPLITIO.split.mysplittest');
        $redisClient->del('SPLITIO.split.mysplittest2');
        $redisClient->del('SPLITIO.split.mysplittest4');
        
        $redisClient->set('SPLITIO.split.mysplittest', $this->split1);
        $redisClient->set('SPLITIO.split.mysplittest2', $this->split2);
        $redisClient->set('SPLITIO.split.mysplittest4', $this->split4);
        $redisClient->sadd('SPLITIO.flagSet.set_1', 'mysplittest2');
        $redisClient->sadd('SPLITIO.flagSet.set_2', 'mysplittest2');
        $redisClient->sadd('SPLITIO.flagSet.set_2', 'mysplittest4');
        $redisClient->sadd('SPLITIO.flagSet.set_5', 'mysplittest');

        $segmentCache = new SegmentCache($cachePool);
        $splitCache = new SplitCache($cachePool);
        $evaluator = new Evaluator($splitCache, $segmentCache);

        $result = $evaluator->evaluateFeaturesByFlagSets('test', '', ['set_1', 'set_2', 'set_3']);

        $this->assertEquals('on', $result['evaluations']['mysplittest2']['treatment']);
        $this->assertEquals('killed', $result['evaluations']['mysplittest4']['treatment']);
        $this->assertFalse(array_key_exists('mysplittest', $result['evaluations']));
        $this->assertGreaterThan(0, $result['latency']);

        $redisClient->del('SPLITIO.flagSet.set_1');
        $redisClient->del('SPLITIO.flagSet.set_2');
        $redisClient->del('SPLITIO.split.mysplittest');
        $redisClient->del('SPLITIO.split.mysplittest2');
        $redisClient->del('SPLITIO.split.mysplittest4');
    }
}
