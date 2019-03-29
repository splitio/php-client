<?php
namespace SplitIO\Test\Suite\Sdk;

use \stdClass;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Common\Di;
use SplitIO\Test\Suite\Redis\ReflectiveTools;
use SplitIO\Component\Cache\ImpressionCache;

class SdkClientTest extends \PHPUnit_Framework_TestCase
{
    private function addSplitsInCache()
    {
        $splitChanges = file_get_contents(__DIR__."/files/splitChanges.json");
        $this->assertJson($splitChanges);

        $splitCache = new SplitCache();

        $splitChanges = json_decode($splitChanges, true);
        $splits = $splitChanges['splits'];

        foreach ($splits as $split) {
            $splitName = $split['name'];
            $this->assertTrue($splitCache->addSplit($splitName, json_encode($split)));
        }
    }

    private function addSegmentsInCache()
    {
        $segmentCache = new SegmentCache();

        //Addinng Employees Segment.
        $segmentEmployeesChanges = file_get_contents(__DIR__ . "/files/segmentEmployeesChanges.json");
        $this->assertJson($segmentEmployeesChanges);
        $segmentData = json_decode($segmentEmployeesChanges, true);
        $this->assertArrayHasKey('employee_1', $segmentCache->addToSegment(
            $segmentData['name'],
            $segmentData['added']
        ));

        //Adding Human Beigns Segment.
        $segmentHumanBeignsChanges = file_get_contents(__DIR__."/files/segmentHumanBeignsChanges.json");
        $this->assertJson($segmentHumanBeignsChanges);
        $segmentData = json_decode($segmentHumanBeignsChanges, true);
        $this->assertArrayHasKey('user1', $segmentCache->addToSegment($segmentData['name'], $segmentData['added']));
    }

    public function testLocalClient()
    {
        $options['splitFile'] = dirname(dirname(__DIR__)).'/files/.splits';
        $splitFactory = \SplitIO\Sdk::factory('localhost', $options);
        $splitSdk = $splitFactory->client();

        $this->assertEquals('treatment_1', $splitSdk->getTreatment('someKey', 'feature_A'));
        $this->assertEquals('treatment_2', $splitSdk->getTreatment('someKey', 'feature_B'));
        $this->assertEquals('treatment_1', $splitSdk->getTreatment('someKey', 'feature_C'));

        $this->assertEquals('control', $splitSdk->getTreatment('someKey', 'invalid_feature'));

        $this->assertTrue($splitSdk->isTreatment('someKey', 'feature_C', 'treatment_1'));
        $this->assertFalse($splitSdk->isTreatment('someKey', 'feature_C', 'invalid_treatment'));
    }

    private function validateLastImpression($redisClient, $feature, $key, $treatment)
    {
        $raw = $redisClient->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $parsed = json_decode($raw, true);
        $this->assertEquals($parsed['i']['f'], $feature);
        $this->assertEquals($parsed['i']['k'], $key);
        $this->assertEquals($parsed['i']['t'], $treatment);
    }

    public function testClient()
    {
        //Testing version string
        $this->assertTrue(is_string(\SplitIO\version()));

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();
        $splitManager = $splitFactory->manager();

        //Populating the cache.
        $this->addSplitsInCache();
        $this->addSegmentsInCache();

        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'sample_feature'));
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');

        $this->assertEquals('off', $splitSdk->getTreatment('invalidKey', 'sample_feature'));
        $this->validateLastImpression($redisClient, 'sample_feature', 'invalidKey', 'off');

        $this->assertEquals('control', $splitSdk->getTreatment('invalidKey', 'invalid_feature'));
        $this->validateLastImpression($redisClient, 'invalid_feature', 'invalidKey', 'control');

        $this->assertTrue($splitSdk->isTreatment('user1', 'sample_feature', 'on'));
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');

        $this->assertFalse($splitSdk->isTreatment('user1', 'sample_feature', 'invalid_treatment'));
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');

        //testing a killed feature. No matter what the key, must return default treatment
        $this->assertEquals('defTreatment', $splitSdk->getTreatment('invalidKey', 'killed_feature'));
        $this->validateLastImpression($redisClient, 'killed_feature', 'invalidKey', 'defTreatment');

        //testing ALL matcher
        $this->assertEquals('on', $splitSdk->getTreatment('invalidKey', 'all_feature'));
        $this->validateLastImpression($redisClient, 'all_feature', 'invalidKey', 'on');

        //testing WHITELIST matcher
        $this->assertEquals('on', $splitSdk->getTreatment('whitelisted_user', 'whitelist_feature'));
        $this->validateLastImpression($redisClient, 'whitelist_feature', 'whitelisted_user', 'on');
        $this->assertEquals('off', $splitSdk->getTreatment('unwhitelisted_user', 'whitelist_feature'));
        $this->validateLastImpression($redisClient, 'whitelist_feature', 'unwhitelisted_user', 'off');

        // testing INVALID matcher
        $this->assertEquals('control', $splitSdk->getTreatment('some_user_key', 'invalid_matcher_feature'));
        $this->validateLastImpression($redisClient, 'invalid_matcher_feature', 'some_user_key', 'control');

        // testing Dependency matcher
        $this->assertEquals('off', $splitSdk->getTreatment('somekey', 'dependency_test'));
        $this->validateLastImpression($redisClient, 'dependency_test', 'somekey', 'off');

        // testing boolean matcher
        $this->assertEquals('on', $splitSdk->getTreatment('True', 'boolean_test'));
        $this->validateLastImpression($redisClient, 'boolean_test', 'True', 'on');

        // testing regex matcher
        $this->assertEquals('on', $splitSdk->getTreatment('abc4', 'regex_test'));
        $this->validateLastImpression($redisClient, 'regex_test', 'abc4', 'on');

        //Assertions GET_TREATMENT_WITH_CONFIG
        $result = $splitSdk->getTreatmentWithConfig('user1', 'sample_feature');
        $this->assertEquals('on', $result['treatment']);
        $this->assertEquals('{"size":15,"test":20}', $result['configurations']);
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');

        $result = $splitSdk->getTreatmentWithConfig('invalidKey', 'sample_feature');
        $this->assertEquals('off', $result['treatment']);
        $this->assertEquals(null, $result['configurations']);
        $this->validateLastImpression($redisClient, 'sample_feature', 'invalidKey', 'off');

        $result = $splitSdk->getTreatmentWithConfig('invalidKey', 'invalid_feature');
        $this->assertEquals('control', $result['treatment']);
        $this->assertEquals(null, $result['configurations']);
        $this->validateLastImpression($redisClient, 'invalid_feature', 'invalidKey', 'control');

        //testing a killed feature. No matter what the key, must return default treatment
        $result = $splitSdk->getTreatmentWithConfig('invalidKey', 'killed_feature');
        $this->assertEquals('defTreatment', $result['treatment']);
        $this->assertEquals('{"size":15,"defTreatment":true}', $result['configurations']);
        $this->validateLastImpression($redisClient, 'killed_feature', 'invalidKey', 'defTreatment');

        //testing ALL matcher
        $result = $splitSdk->getTreatmentWithConfig('invalidKey', 'all_feature');
        $this->assertEquals('on', $result['treatment']);
        $this->assertEquals(null, $result['configurations']);
        $this->validateLastImpression($redisClient, 'all_feature', 'invalidKey', 'on');

        //Assertions GET_TREATMENTS
        $result = $splitSdk->getTreatments('user1', array('sample_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('on', $result['sample_feature']);
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');

        $result = $splitSdk->getTreatments('invalidKey', array('sample_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('off', $result['sample_feature']);
        $this->validateLastImpression($redisClient, 'sample_feature', 'invalidKey', 'off');

        $result = $splitSdk->getTreatments('invalidKey', array('invalid_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('control', $result['invalid_feature']);
        $this->validateLastImpression($redisClient, 'invalid_feature', 'invalidKey', 'control');

        //testing a killed feature. No matter what the key, must return default treatment
        $result = $splitSdk->getTreatments('invalidKey', array('killed_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('defTreatment', $result['killed_feature']);
        $this->validateLastImpression($redisClient, 'killed_feature', 'invalidKey', 'defTreatment');

        //testing ALL matcher
        $result = $splitSdk->getTreatments('invalidKey', array('all_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('on', $result['all_feature']);
        $this->validateLastImpression($redisClient, 'all_feature', 'invalidKey', 'on');

        //testing multiple splitNames
        $result = $splitSdk->getTreatments('invalidKey', array(
            'all_feature',
            'killed_feature',
            'invalid_feature',
            'sample_feature'
        ));
        $this->assertEquals(4, count($result));
        $this->assertEquals('on', $result['all_feature']);
        $this->assertEquals('defTreatment', $result['killed_feature']);
        $this->assertEquals('control', $result['invalid_feature']);
        $this->assertEquals('off', $result['sample_feature']);

        //Assertions GET_TREATMENTS_WITH_CONFIG
        $result = $splitSdk->getTreatmentsWithConfig('user1', array('sample_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('on', $result['sample_feature']['treatment']);
        $this->assertEquals('{"size":15,"test":20}', $result['sample_feature']['configurations']);
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');

        $result = $splitSdk->getTreatmentsWithConfig('invalidKey', array('sample_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('off', $result['sample_feature']['treatment']);
        $this->assertEquals(null, $result['sample_feature']['configurations']);
        $this->validateLastImpression($redisClient, 'sample_feature', 'invalidKey', 'off');

        $result = $splitSdk->getTreatmentsWithConfig('invalidKey', array('invalid_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('control', $result['invalid_feature']['treatment']);
        $this->assertEquals(null, $result['invalid_feature']['configurations']);
        $this->validateLastImpression($redisClient, 'invalid_feature', 'invalidKey', 'control');

        //testing a killed feature. No matter what the key, must return default treatment
        $result = $splitSdk->getTreatmentsWithConfig('invalidKey', array('killed_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('defTreatment', $result['killed_feature']['treatment']);
        $this->assertEquals('{"size":15,"defTreatment":true}', $result['killed_feature']['configurations']);
        $this->validateLastImpression($redisClient, 'killed_feature', 'invalidKey', 'defTreatment');

        //testing ALL matcher
        $result = $splitSdk->getTreatmentsWithConfig('invalidKey', array('all_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('on', $result['all_feature']['treatment']);
        $this->assertEquals(null, $result['all_feature']['configurations']);
        $this->validateLastImpression($redisClient, 'all_feature', 'invalidKey', 'on');

        //testing multiple splitNames
        $result = $splitSdk->getTreatmentsWithConfig('invalidKey', array(
            'all_feature',
            'killed_feature',
            'invalid_feature',
            'sample_feature'
        ));
        $this->assertEquals(4, count($result));
        $this->assertEquals('on', $result['all_feature']['treatment']);
        $this->assertEquals(null, $result['all_feature']['configurations']);
        $this->assertEquals('defTreatment', $result['killed_feature']['treatment']);
        $this->assertEquals('{"size":15,"defTreatment":true}', $result['killed_feature']['configurations']);
        $this->assertEquals('control', $result['invalid_feature']['treatment']);
        $this->assertEquals(null, $result['invalid_feature']['configurations']);
        $this->assertEquals('off', $result['sample_feature']['treatment']);
        $this->assertEquals(null, $result['sample_feature']['configurations']);

        //Assertions Manager
        $result = $splitManager->split('all_feature');
        $this->assertEquals('all_feature', $result->getName());
        $this->assertEquals(null, $result->getTrafficType());
        $this->assertEquals(false, $result->getKilled());
        $this->assertEquals(2, count($result->getTreatments()));
        $this->assertEquals(-1, $result->getChangeNumber());
        $this->assertEquals(new StdClass, $result->getConfigurations()['on']);
        $this->assertEquals(new StdClass, $result->getConfigurations()['off']);

        $result = $splitManager->split('killed_feature');
        $this->assertEquals('killed_feature', $result->getName());
        $this->assertEquals(null, $result->getTrafficType());
        $this->assertEquals(true, $result->getKilled());
        $this->assertEquals(2, count($result->getTreatments()));
        $this->assertEquals(-1, $result->getChangeNumber());
        $this->assertEquals(new StdClass, $result->getConfigurations()['off']);
        $this->assertEquals('{"size":15,"defTreatment":true}', $result->getConfigurations()['defTreatment']);

        $result = $splitManager->split('sample_feature');
        $this->assertEquals('sample_feature', $result->getName());
        $this->assertEquals(null, $result->getTrafficType());
        $this->assertEquals(false, $result->getKilled());
        $this->assertEquals(2, count($result->getTreatments()));
        $this->assertEquals(-1, $result->getChangeNumber());
        $this->assertEquals(new StdClass, $result->getConfigurations()['off']);
        $this->assertEquals('{"size":15,"test":20}', $result->getConfigurations()['on']);

        $this->assertEquals(41, count($splitManager->splitNames()));

        $this->assertEquals(41, count($splitManager->splits()));
    }

    /**
     * @depends testClient
     */
    public function testCustomLog()
    {
        // create a log channel
        $log = new Logger('SplitIO');
        $log->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::INFO));

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('psr3-instance' => $log),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();
        $this->addSegmentsInCache();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'sample_feature'));
        $this->assertEquals('off', $splitSdk->getTreatment('invalidKey', 'sample_feature'));
        $this->assertEquals('control', $splitSdk->getTreatment('invalidKey', 'invalid_feature'));

        $this->assertTrue($splitSdk->isTreatment('user1', 'sample_feature', 'on'));
        $this->assertFalse($splitSdk->isTreatment('user1', 'sample_feature', 'invalid_treatment'));
    }

    public function testInvalidCacheAdapter()
    {
        $this->setExpectedException('\SplitIO\Exception\Exception');

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'invalidAdapter')
        );

        //Initializing the SDK instance.
        \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
    }

    public function testCacheExceptionReturnsControl()
    {
        $log = new Logger('SplitIO');
        $log->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::INFO));

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('psr3-instance' => $log),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        $cachePoolMethods = array(
            'getItem', 'getItems', 'hasItem', 'clear', 'deleteItem', 'deleteItems',
            'save', 'saveDeferred', 'commit', 'saveItemOnList', 'removeItemOnList',
            'getItemOnList', 'getItemsOnList', 'isItemOnList', 'getItemsRandomlyOnList',
            'getKeys', 'incrementeKey', 'getSet', 'rightPushInList'
        );
        $cachePool = $this
            ->getMockBuilder('\SplitIO\Component\Cache\Pool')
            ->disableOriginalConstructor()
            ->setMethods($cachePoolMethods)
            ->getMock();

        foreach ($cachePoolMethods as $method) {
            $cachePool->method($method)
                ->will($this->throwException(new \Exception()));
        }

        Di::setCache($cachePool);

        $treatment = $splitSdk->getTreatment('key1', 'feature1');
        $this->assertEquals($treatment, 'control');
    }

    public function testGetTreatmentsWithDistinctFeatures()
    {

        //Testing version string
        $this->assertTrue(is_string(\SplitIO\version()));

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();
        $this->addSegmentsInCache();

        $treatmentResult = $splitSdk->getTreatments('user1', ['sample_feature', 'invalid_feature'], null);

        //Assertions
        $this->assertEquals(2, count(array_keys($treatmentResult)));

        $this->assertEquals('on', $treatmentResult['sample_feature']);
        $this->assertEquals('control', $treatmentResult['invalid_feature']);

        //Check impressions generated
        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());
        $this->validateLastImpression($redisClient, 'invalid_feature', 'user1', 'control');
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');
    }

    public function testGetTreatmentsWithRepeteadedFeatures()
    {

        //Testing version string
        $this->assertTrue(is_string(\SplitIO\version()));

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();
        $this->addSegmentsInCache();

        $treatmentResult = $splitSdk->getTreatments('user1', ['sample_feature', 'invalid_feature', 'sample_feature',
        'sample_feature'], null);

        //Assertions
        $this->assertEquals(2, count(array_keys($treatmentResult)));

        $this->assertEquals('on', $treatmentResult['sample_feature']);
        $this->assertEquals('control', $treatmentResult['invalid_feature']);

        // Check impressions
        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());
        $this->validateLastImpression($redisClient, 'invalid_feature', 'user1', 'control');
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');
    }

    public function testGetTreatmentsWithRepeteadedAndNullFeatures()
    {

        //Testing version string
        $this->assertTrue(is_string(\SplitIO\version()));

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        $this->addSplitsInCache();
        $this->addSegmentsInCache();

        $treatmentResult = $splitSdk->getTreatments('user1', ['sample_feature', null, 'invalid_feature',
        'sample_feature', null, 'sample_feature'], null);

        //Assertions
        $this->assertEquals(2, count(array_keys($treatmentResult)));

        $this->assertEquals('on', $treatmentResult['sample_feature']);
        $this->assertEquals('control', $treatmentResult['invalid_feature']);

        //Check impressions
        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());
        $this->validateLastImpression($redisClient, 'invalid_feature', 'user1', 'control');
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');
    }
}
