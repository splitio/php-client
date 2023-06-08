<?php
namespace SplitIO\Test\Suite\Sdk;

use \stdClass;
use SplitIO\Component\Common\Di;
use SplitIO\Test\Suite\Redis\ReflectiveTools;
use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Component\Cache\Storage\Adapter;
use SplitIO\Component\Cache\Storage\Adapter\PRedis;
use SplitIO\Component\Cache\Pool;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Sdk\Client;

use SplitIO\Test\Suite\Sdk\Helpers\CustomLogger;
use SplitIO\Test\Utils;

class SdkClientTest extends \PHPUnit\Framework\TestCase
{
    public function testLocalClient()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
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

    public function testLocalClientYAML()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        $options['splitFile'] = dirname(dirname(__DIR__)).'/files/splits.yml';
        $splitFactory = \SplitIO\Sdk::factory('localhost', $options);
        $splitSdk = $splitFactory->client();
        $splitManager = $splitFactory->manager();

        $this->assertEquals('off', $splitSdk->getTreatment('only_key', 'my_feature'));
        $this->assertEquals('control', $splitSdk->getTreatment('invalid_key', 'my_feature'));
        $this->assertEquals('on', $splitSdk->getTreatment('key', 'my_feature'));
        $this->assertEquals('on', $splitSdk->getTreatment('key2', 'other_feature'));
        $this->assertEquals('on', $splitSdk->getTreatment('test', 'other_feature_2'));
        $this->assertEquals('off', $splitSdk->getTreatment('key', 'other_feature_3'));
        $this->assertEquals('on', $splitSdk->getTreatment('key_whitelist', 'other_feature_3'));
        $this->assertEquals('control', $splitSdk->getTreatment(true, 'other_feature_3'));
        $this->assertEquals('control', $splitSdk->getTreatment('some', null));

        $result = $splitSdk->getTreatments('only_key', array('my_feature', 'other_feature'));
        $this->assertEquals('off', $result["my_feature"]);
        $this->assertEquals('control', $result["other_feature"]);

        $result = $splitSdk->getTreatments(true, array('my_feature', 'other_feature'));
        $this->assertEquals('control', $result["my_feature"]);
        $this->assertEquals('control', $result["other_feature"]);

        $result = $splitSdk->getTreatments(true, array(true, 'other_feature'));
        $this->assertEquals('control', $result["other_feature"]);

        $result = $splitSdk->getTreatments("some", array());
        $this->assertEquals(array(), $result);

        $result = $splitSdk->getTreatments("some", null);
        $this->assertEquals(array(), $result);

        $result = $splitSdk->getTreatmentWithConfig('only_key', 'my_feature');
        $this->assertEquals('off', $result["treatment"]);
        $this->assertEquals(
            '{"desc" : "this applies only to OFF and only for only_key. The rest will receive ON"}',
            $result["config"]
        );

        $result = $splitSdk->getTreatmentWithConfig('invalid_key', 'my_feature');
        $this->assertEquals('control', $result["treatment"]);
        $this->assertEquals(null, $result["config"]);

        $result = $splitSdk->getTreatmentWithConfig('key', 'my_feature');
        $this->assertEquals('on', $result["treatment"]);
        $this->assertEquals(
            '{"desc" : "this applies only to ON treatment"}',
            $result["config"]
        );

        $result = $splitSdk->getTreatmentWithConfig('key2', 'other_feature');
        $this->assertEquals('on', $result["treatment"]);
        $this->assertEquals(null, $result["config"]);

        $result = $splitSdk->getTreatmentWithConfig('test', 'other_feature_2');
        $this->assertEquals('on', $result["treatment"]);
        $this->assertEquals(null, $result["config"]);

        $result = $splitSdk->getTreatmentWithConfig('key', 'other_feature_3');
        $this->assertEquals('off', $result["treatment"]);
        $this->assertEquals(null, $result["config"]);

        $result = $splitSdk->getTreatmentWithConfig('key_whitelist', 'other_feature_3');
        $this->assertEquals('on', $result["treatment"]);
        $this->assertEquals(null, $result["config"]);

        $result = $splitSdk->getTreatmentWithConfig(true, 'other_feature_3');
        $this->assertEquals('control', $result["treatment"]);
        $this->assertEquals(null, $result["config"]);

        $result = $splitSdk->getTreatmentWithConfig('key_whitelist', true);
        $this->assertEquals('control', $result["treatment"]);
        $this->assertEquals(null, $result["config"]);

        $result = $splitSdk->getTreatmentWithConfig('some', null);
        $this->assertEquals('control', $result["treatment"]);
        $this->assertEquals(null, $result["config"]);

        $result = $splitSdk->getTreatmentsWithConfig('only_key', array('my_feature', 'other_feature'));
        $this->assertEquals('off', $result['my_feature']["treatment"]);
        $this->assertEquals(
            '{"desc" : "this applies only to OFF and only for only_key. The rest will receive ON"}',
            $result['my_feature']["config"]
        );
        $this->assertEquals('control', $result['other_feature']["treatment"]);
        $this->assertEquals(null, $result['other_feature']["config"]);

        $result = $splitSdk->getTreatmentsWithConfig(true, array('my_feature', 'other_feature'));
        $this->assertEquals('control', $result['my_feature']["treatment"]);
        $this->assertEquals(null, $result['my_feature']["config"]);
        $this->assertEquals('control', $result['other_feature']["treatment"]);
        $this->assertEquals(null, $result['other_feature']["config"]);

        $result = $splitSdk->getTreatmentsWithConfig('only_key', array('my_feature', true));
        $this->assertEquals('off', $result['my_feature']["treatment"]);
        $this->assertEquals(
            '{"desc" : "this applies only to OFF and only for only_key. The rest will receive ON"}',
            $result['my_feature']["config"]
        );

        $result = $splitSdk->getTreatmentsWithConfig('some', array());
        $this->assertEquals(array(), $result);

        $result = $splitSdk->getTreatmentsWithConfig('some', null);
        $this->assertEquals(array(), $result);

        $this->assertEquals(4, count($splitManager->splitNames()));

        $splitView = $splitManager->split("my_feature");
        $this->assertEquals("my_feature", $splitView->getName());
        $this->assertEquals(false, $splitView->getKilled());
        $this->assertEquals(2, count($splitView->getTreatments()));
        $config = $splitView->getConfigs();
        $this->assertEquals(
            '{"desc" : "this applies only to OFF and only for only_key. The rest will receive ON"}',
            $config['off']
        );
        $this->assertEquals(
            '{"desc" : "this applies only to ON treatment"}',
            $config['on']
        );

        $splitView = $splitManager->split("other_feature");
        $this->assertEquals("other_feature", $splitView->getName());
        $this->assertEquals(false, $splitView->getKilled());
        $this->assertEquals(1, count($splitView->getTreatments()));
        $config = $splitView->getConfigs();
        $this->assertEquals(
            new StdClass,
            $config
        );

        $splitView = $splitManager->split("other_feature_2");
        $this->assertEquals("other_feature_2", $splitView->getName());
        $this->assertEquals(false, $splitView->getKilled());
        $this->assertEquals(1, count($splitView->getTreatments()));
        $config = $splitView->getConfigs();
        $this->assertEquals(
            new StdClass,
            $config
        );

        $splitView = $splitManager->split("other_feature_3");
        $this->assertEquals("other_feature_3", $splitView->getName());
        $this->assertEquals(false, $splitView->getKilled());
        $this->assertEquals(2, count($splitView->getTreatments()));
        $config = $splitView->getConfigs();
        $this->assertEquals(
            new StdClass,
            $config
        );
    }

    private function validateLastImpression(
        $redisClient,
        $feature,
        $key,
        $treatment,
        $machineName = 'unknown',
        $machineIP = 'unknown'
    ) {
        $raw = $redisClient->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $parsed = json_decode($raw, true);
        $this->assertEquals($parsed['i']['f'], $feature);
        $this->assertEquals($parsed['i']['k'], $key);
        $this->assertEquals($parsed['i']['t'], $treatment);
        $this->assertEquals($parsed['m']['i'], $machineIP);
        $this->assertEquals($parsed['m']['n'], $machineName);
    }

    public function testClient()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        //Testing version string
        $this->assertTrue(is_string(\SplitIO\version()));

        $parameters = array(
            'scheme' => 'redis',
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
            'timeout' => 881,
        );
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();
        $splitManager = $splitFactory->manager();

        //Populating the cache.
        Utils\Utils::addSplitsInCache(file_get_contents(__DIR__."/files/splitChanges.json"));
        Utils\Utils::addSegmentsInCache(file_get_contents(__DIR__."/files/segmentEmployeesChanges.json"));
        Utils\Utils::addSegmentsInCache(file_get_contents(__DIR__."/files/segmentHumanBeignsChanges.json"));

        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'sample_feature'));
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');

        $this->assertEquals('off', $splitSdk->getTreatment('invalidKey', 'sample_feature'));
        $this->validateLastImpression($redisClient, 'sample_feature', 'invalidKey', 'off');

        $this->assertEquals('control', $splitSdk->getTreatment('invalidKey', 'invalid_feature'));

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
        $this->assertEquals('{"size":15,"test":20}', $result['config']);
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');

        $result = $splitSdk->getTreatmentWithConfig('invalidKey', 'sample_feature');
        $this->assertEquals('off', $result['treatment']);
        $this->assertEquals(null, $result['config']);
        $this->validateLastImpression($redisClient, 'sample_feature', 'invalidKey', 'off');

        $result = $splitSdk->getTreatmentWithConfig('invalidKey', 'invalid_feature');
        $this->assertEquals('control', $result['treatment']);
        $this->assertEquals(null, $result['config']);

        //testing a killed feature. No matter what the key, must return default treatment
        $result = $splitSdk->getTreatmentWithConfig('invalidKey', 'killed_feature');
        $this->assertEquals('defTreatment', $result['treatment']);
        $this->assertEquals('{"size":15,"defTreatment":true}', $result['config']);
        $this->validateLastImpression($redisClient, 'killed_feature', 'invalidKey', 'defTreatment');

        //testing ALL matcher
        $result = $splitSdk->getTreatmentWithConfig('invalidKey', 'all_feature');
        $this->assertEquals('on', $result['treatment']);
        $this->assertEquals(null, $result['config']);
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

        // testing INVALID matcher
        $result = $splitSdk->getTreatments('some_user_key', array('invalid_matcher_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('control', $result['invalid_matcher_feature']);
        $this->validateLastImpression($redisClient, 'invalid_matcher_feature', 'some_user_key', 'control');

        //Assertions GET_TREATMENTS_WITH_CONFIG
        $result = $splitSdk->getTreatmentsWithConfig('user1', array('sample_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('on', $result['sample_feature']['treatment']);
        $this->assertEquals('{"size":15,"test":20}', $result['sample_feature']['config']);
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');

        $result = $splitSdk->getTreatmentsWithConfig('invalidKey', array('sample_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('off', $result['sample_feature']['treatment']);
        $this->assertEquals(null, $result['sample_feature']['config']);
        $this->validateLastImpression($redisClient, 'sample_feature', 'invalidKey', 'off');

        $result = $splitSdk->getTreatmentsWithConfig('invalidKey', array('invalid_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('control', $result['invalid_feature']['treatment']);
        $this->assertEquals(null, $result['invalid_feature']['config']);

        //testing a killed feature. No matter what the key, must return default treatment
        $result = $splitSdk->getTreatmentsWithConfig('invalidKey', array('killed_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('defTreatment', $result['killed_feature']['treatment']);
        $this->assertEquals('{"size":15,"defTreatment":true}', $result['killed_feature']['config']);
        $this->validateLastImpression($redisClient, 'killed_feature', 'invalidKey', 'defTreatment');

        //testing ALL matcher
        $result = $splitSdk->getTreatmentsWithConfig('invalidKey', array('all_feature'));
        $this->assertEquals(1, count($result));
        $this->assertEquals('on', $result['all_feature']['treatment']);
        $this->assertEquals(null, $result['all_feature']['config']);
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
        $this->assertEquals(null, $result['all_feature']['config']);
        $this->assertEquals('defTreatment', $result['killed_feature']['treatment']);
        $this->assertEquals('{"size":15,"defTreatment":true}', $result['killed_feature']['config']);
        $this->assertEquals('control', $result['invalid_feature']['treatment']);
        $this->assertEquals(null, $result['invalid_feature']['config']);
        $this->assertEquals('off', $result['sample_feature']['treatment']);
        $this->assertEquals(null, $result['sample_feature']['config']);

        //Assertions Manager
        $result = $splitManager->split('all_feature');
        $this->assertEquals('all_feature', $result->getName());
        $this->assertEquals(null, $result->getTrafficType());
        $this->assertEquals(false, $result->getKilled());
        $this->assertEquals(2, count($result->getTreatments()));
        $this->assertEquals(-1, $result->getChangeNumber());
        $this->assertEquals(new StdClass, $result->getConfigs());

        $result = $splitManager->split('killed_feature');
        $this->assertEquals('killed_feature', $result->getName());
        $this->assertEquals(null, $result->getTrafficType());
        $this->assertEquals(true, $result->getKilled());
        $this->assertEquals(2, count($result->getTreatments()));
        $this->assertEquals(-1, $result->getChangeNumber());
        $configs = $result->getConfigs();
        $this->assertEquals('{"size":15,"defTreatment":true}', $configs['defTreatment']);
        $configs = $result->getConfigs();
        $this->assertEquals('{"size":15,"test":20}', $configs['off']);

        $result = $splitManager->split('sample_feature');
        $this->assertEquals('sample_feature', $result->getName());
        $this->assertEquals(null, $result->getTrafficType());
        $this->assertEquals(false, $result->getKilled());
        $this->assertEquals(2, count($result->getTreatments()));
        $this->assertEquals(-1, $result->getChangeNumber());
        $configs = $result->getConfigs();
        $this->assertEquals('{"size":15,"test":20}', $configs['on']);
    }

    /**
     * @depends testClient
     */
    public function testCustomLog()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        // create a log channel
        $log = $this
            ->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('warning', 'debug', 'error', 'info', 'critical', 'emergency',
                'alert', 'notice', 'write', 'log'))
            ->getMock();

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('psr3-instance' => $log),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        Utils\Utils::addSplitsInCache(file_get_contents(__DIR__."/files/splitChanges.json"));
        Utils\Utils::addSegmentsInCache(file_get_contents(__DIR__."/files/segmentEmployeesChanges.json"));
        Utils\Utils::addSegmentsInCache(file_get_contents(__DIR__."/files/segmentHumanBeignsChanges.json"));

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'sample_feature'));
        $this->assertEquals('off', $splitSdk->getTreatment('invalidKey', 'sample_feature'));
        $this->assertEquals('control', $splitSdk->getTreatment('invalidKey', 'invalid_feature'));

        $this->assertTrue($splitSdk->isTreatment('user1', 'sample_feature', 'on'));
        $this->assertFalse($splitSdk->isTreatment('user1', 'sample_feature', 'invalid_treatment'));
    }

    public function testInvalidCacheAdapter()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        $this->expectException('\SplitIO\Exception\Exception');

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'invalidAdapter')
        );

        //Initializing the SDK instance.
        \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
    }

    public function testCacheExceptionReturnsControl()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);

        $log = $this
            ->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('warning', 'debug', 'error', 'info', 'critical', 'emergency',
                'alert', 'notice', 'write', 'log'))
            ->getMock();

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array();

        $sdkConfig = array(
            'log' => array('psr3-instance' => $log),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        $cachePoolMethods = array(
            'get', 'fetchMany', 'isItemOnList', 'getKeys', 'rightPushInList', 'expireKey'
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
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        //Testing version string
        $this->assertTrue(is_string(\SplitIO\version()));

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options),
            'IPAddressesEnabled' => false
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        Utils\Utils::addSplitsInCache(file_get_contents(__DIR__."/files/splitChanges.json"));
        Utils\Utils::addSegmentsInCache(file_get_contents(__DIR__."/files/segmentEmployeesChanges.json"));
        Utils\Utils::addSegmentsInCache(file_get_contents(__DIR__."/files/segmentHumanBeignsChanges.json"));

        $treatmentResult = $splitSdk->getTreatments('user1', array('sample_feature', 'invalid_feature'), null);

        //Assertions
        $this->assertEquals(2, count(array_keys($treatmentResult)));

        $this->assertEquals('on', $treatmentResult['sample_feature']);
        $this->assertEquals('control', $treatmentResult['invalid_feature']);

        //Check impressions generated
        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on', 'NA', 'NA');
    }

    public function testGetTreatmentsWithRepeteadedFeatures()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);

        //Testing version string
        $this->assertTrue(is_string(\SplitIO\version()));

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options),
            'ipAddress' => '1.2.3.4'
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        Utils\Utils::addSplitsInCache(file_get_contents(__DIR__."/files/splitChanges.json"));
        Utils\Utils::addSegmentsInCache(file_get_contents(__DIR__."/files/segmentEmployeesChanges.json"));
        Utils\Utils::addSegmentsInCache(file_get_contents(__DIR__."/files/segmentHumanBeignsChanges.json"));

        $treatmentResult = $splitSdk->getTreatments('user1', array('sample_feature', 'invalid_feature',
        'sample_feature', 'sample_feature'), null);

        //Assertions
        $this->assertEquals(2, count(array_keys($treatmentResult)));

        $this->assertEquals('on', $treatmentResult['sample_feature']);
        $this->assertEquals('control', $treatmentResult['invalid_feature']);

        // Check impressions
        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on', 'ip-1-2-3-4', '1.2.3.4');
    }

    public function testGetTreatmentsWithRepeteadedAndNullFeatures()
    {
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        Di::set('ipAddress', null); // unset ipaddress from previous test

        //Testing version string
        $this->assertTrue(is_string(\SplitIO\version()));

        $parameters = array('scheme' => 'redis', 'host' => REDIS_HOST, 'port' => REDIS_PORT, 'timeout' => 881);
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );

        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();

        //Populating the cache.
        Utils\Utils::addSplitsInCache(file_get_contents(__DIR__."/files/splitChanges.json"));
        Utils\Utils::addSegmentsInCache(file_get_contents(__DIR__."/files/segmentEmployeesChanges.json"));
        Utils\Utils::addSegmentsInCache(file_get_contents(__DIR__."/files/segmentHumanBeignsChanges.json"));

        $treatmentResult = $splitSdk->getTreatments('user1', array('sample_feature', null, 'invalid_feature',
        'sample_feature', null, 'sample_feature'), null);

        //Assertions
        $this->assertEquals(2, count(array_keys($treatmentResult)));

        $this->assertEquals('on', $treatmentResult['sample_feature']);
        $this->assertEquals('control', $treatmentResult['invalid_feature']);

        //Check impressions
        $redisClient = ReflectiveTools::clientFromCachePool(Di::getCache());
        $this->validateLastImpression($redisClient, 'sample_feature', 'user1', 'on');
    }

    public function testGetTreatmentsFetchesSplitsInOneCall()
    {
        // Set redis-library client mock
        $predisMock = $this
            ->getMockBuilder('\Predis\Client')
            ->disableOriginalConstructor()
            ->setMethods(array('get', 'mget', 'rpush', 'incr'))
            ->getMock();

        // Create an adapter and inject mock via reflection
        $adapter = new PRedis(array('parameters' => array()));
        $adapterReflection = new \ReflectionClass($adapter);
        $clientProperty = $adapterReflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($adapter, $predisMock);

        // Create a pool and inject the adapter via reflection
        $pool = new Pool(array('adapter' => array('name' => 'predis', 'options' => array('parameters' => array()))));
        $poolReflection = new \ReflectionClass($pool);
        $adapterProperty = $poolReflection->getProperty('adapter');
        $adapterProperty->setAccessible(true);
        $adapterProperty->setValue($pool, $adapter);

        // use the newly created pool as cache
        Di::setCache($pool);

        // Assert that MGET is called with the 3 splits.
        $predisMock->expects($this->once())
            ->method('mget')
            ->with($this->equalTo(array('SPLITIO.split.split1', 'SPLITIO.split.split2', 'SPLITIO.split.split3')));

        // Assert that GET is never called
        $predisMock->expects($this->never())
            ->method('get');

        $client = new Client();
        $client->getTreatments('key1', array('split1', 'split2', 'split3'));
    }

    public function testDatabaseParameterIsPropagatedToPredisClient() {
        $database = 1;
        
        Di::set(Di::KEY_FACTORY_TRACKER, false);

        $parameters = array(
            'scheme' => 'redis',
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
            'timeout' => 881,
            'database' => $database
        );
        $options = array('prefix' => TEST_PREFIX);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'predis', 'parameters' => $parameters, 'options' => $options)
        );
        
        //Initializing the SDK instance.
        $splitFactory = \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
        $splitSdk = $splitFactory->client();
        
        //Storing the split in Redis in a non-default database
        $predis = new \Predis\Client([
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
            'database' => $database,
        ], ['prefix' => TEST_PREFIX]);
        
        $split = [
            "orgId" => null,
            "environment" => null,
            "trafficTypeId" => null,
            "trafficTypeName" => null,
            "name" => "simple-split",
            "seed" => -1222652054,
            "status" => "ACTIVE",
            "killed" => false,
            "defaultTreatment" => "on",
            "conditions" => []
        ];
        $splitName = $split['name'];
        $predis->set("SPLITIO.split." . $splitName, json_encode($split));
        $predis->set("SPLITIO.splits.till", 1457726098069);
        
        //Asserting we can read the split even in a different database
        $this->assertEquals('on', $splitSdk->getTreatment('user1', $splitName));
    }

    public static function tearDownAfterClass(): void
    {
        Utils\Utils::cleanCache();
    }
}
