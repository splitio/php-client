<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Component\Common\Di;
use SplitIO\Test\Suite\Redis\ReflectiveTools;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Split;

class SplitTest extends \PHPUnit\Framework\TestCase
{
    public $split1 = <<<EOD
{"trafficTypeName":"user","name":"mysplittest","trafficAllocation":100,
"trafficAllocationSeed":-285565213,"seed":-1992295819,"status":"ACTIVE","killed":false,
"defaultTreatment":"off","changeNumber":1494593336752,"algo":2,"conditions":[{"conditionType"
:"ROLLOUT","matcherGroup":{"combiner":"AND","matchers":[{"keySelector":{"trafficType"
:"user","attribute":null},"matcherType":"ALL_KEYS","negate":false,"userDefinedSegmentMatcherData":null,
"whitelistMatcherData":null,"unaryNumericMatcherData":null,"betweenMatcherData":null,"booleanMatcherData"
:null,"dependencyMatcherData":null,"stringMatcherData":null}]},"partitions":[{"treatment":"on","size":0}
,{"treatment":"off","size":100}],"label":"default rule"}]}
EOD;

    public $split2 = <<<EOD
{"trafficTypeName":"user","name":"mysplittest2","trafficAllocation":100,
"trafficAllocationSeed":1252392550,"seed":971538037,"status":"ACTIVE","killed":false,
"defaultTreatment":"off","changeNumber":1494593352077,"algo":2,"conditions":[{"conditionType"
:"ROLLOUT","matcherGroup":{"combiner":"AND","matchers":[{"keySelector":{"trafficType"
:"user","attribute":null},"matcherType":"ALL_KEYS","negate":false,"userDefinedSegmentMatcherData":
null,"whitelistMatcherData":null,"unaryNumericMatcherData":null,"betweenMatcherData":null,
"booleanMatcherData":null,"dependencyMatcherData":null,"stringMatcherData":null}]},"partitions"
:[{"treatment":"on","size":0},{"treatment":"off","size":100}],"label":"default rule"}
],"configurations":{"on":"{\"color\": \"blue\",\"size\": 13}"}}
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
        \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);

        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());

        $redisClient->del('SPLITIO.split.mysplittest');

        $redisClient->set('SPLITIO.split.mysplittest', $this->split1);

        $splitCache = new SplitCache();
        $splitRepresentation = $splitCache->getSplit('mysplittest');

        $split = new Split(json_decode($splitRepresentation, true));

        $this->assertEquals('mysplittest', $split->getName());
        $this->assertEquals(null, $split->getConfigurations());

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
        \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);

        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());

        $redisClient->del('SPLITIO.split.mysplittest2');

        $redisClient->set('SPLITIO.split.mysplittest2', $this->split2);

        $splitCache = new SplitCache();
        $splitRepresentation = $splitCache->getSplit('mysplittest2');
        $split = new Split(json_decode($splitRepresentation, true));

        $this->assertEquals('mysplittest2', $split->getName());
        $this->assertNotNull($split->getConfigurations());
        $configs = $split->getConfigurations();
        $this->assertEquals($configs['on'], '{"color": "blue","size": 13}');

        $redisClient->del('SPLITIO.split.mysplittest2');
    }
}
