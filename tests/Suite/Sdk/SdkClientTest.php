<?php
namespace SplitIO\Test\Suite\Sdk;

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\SplitCache;

class SdkClientTest extends \PHPUnit_Framework_TestCase
{
    private function addSplitsInCache()
    {
        $splitChanges = file_get_contents(__DIR__."/files/splitChanges.json");
        echo $splitChanges;
        $this->assertJson($splitChanges);

        $splitCache = new SplitCache();

        $splitChanges = json_decode($splitChanges, true);
        $splits = $splitChanges['splits'];

        foreach ($splits as $split) {
            $splitName = $split['name'];
            echo "NAME: $splitName\n";
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
        $this->assertArrayHasKey('employee_1', $segmentCache->addToSegment($segmentData['name'], $segmentData['added']));

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

        //Populating the cache.
        $this->addSplitsInCache();
        $this->addSegmentsInCache();

        //Assertions
        $this->assertEquals('on', $splitSdk->getTreatment('user1', 'sample_feature'));
        $this->assertEquals('off', $splitSdk->getTreatment('invalidKey', 'sample_feature'));
        $this->assertEquals('control', $splitSdk->getTreatment('invalidKey', 'invalid_feature'));

        $this->assertTrue($splitSdk->isTreatment('user1', 'sample_feature', 'on'));
        $this->assertFalse($splitSdk->isTreatment('user1', 'sample_feature', 'invalid_treatment'));

        //testing a killed feature. No matter what the key, must return default treatment
        $this->assertEquals('defTreatment', $splitSdk->getTreatment('invalidKey', 'killed_feature'));

        //testing ALL matcher
        $this->assertEquals('on', $splitSdk->getTreatment('invalidKey', 'all_feature'));

        //testing WHITELIST matcher
        $this->assertEquals('on', $splitSdk->getTreatment('whitelisted_user', 'whitelist_feature'));
        $this->assertEquals('off', $splitSdk->getTreatment('unwhitelisted_user', 'whitelist_feature'));

        // testing INVALID matcher
        $this->assertEquals('control', $splitSdk->getTreatment('some_user_key', 'invalid_matcher_feature'));

        // testing Dependency matcher
        $this->assertEquals('off', $splitSdk->getTreatment('somekey', 'dependency_test'));

        // testing boolean matcher
        $this->assertEquals('on', $splitSdk->getTreatment('True', 'boolean_test'));

        // testing regex matcher
        $this->assertEquals('on', $splitSdk->getTreatment('abc4', 'regex_test'));
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
        $this->setExpectedException(\SplitIO\Exception\Exception::class);

        $sdkConfig = array(
            'log' => array('adapter' => 'stdout'),
            'cache' => array('adapter' => 'invalidAdapter')
        );

        //Initializing the SDK instance.
        \SplitIO\Sdk::factory('asdqwe123456', $sdkConfig);
    }
}
