<?php
namespace SplitIO\Test\Suite\Redis;

use SplitIO\Component\Cache\EventsCache;
use SplitIO\Component\Cache\Pool;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Common\Di;
use SplitIO\Component\Log\Handler\Stdout;
use SplitIO\Component\Log\Logger;
use SplitIO\Component\Log\LogLevelEnum;
use SplitIO\Sdk\Events\EventDTO;
use SplitIO\Sdk\Events\EventQueueMessage;
use SplitIO\Sdk\QueueMetadataMessage;

use SplitIO\Test\Utils;

class CacheInterfacesTest extends \PHPUnit\Framework\TestCase
{

    public function testDiLog()
    {
        $logAdapter = new Stdout();

        $logger = new Logger($logAdapter, LogLevelEnum::INFO);

        Di::getInstance()->setLogger($logger);

        $this->assertTrue(true);
    }

    /**
     * @depends testDiLog
     */
    public function testDiCache()
    {
        try {
            $parameters = array(
                'host' => REDIS_HOST,
                'port' => REDIS_PORT,
            );

            $cachePool = new Pool(array('adapter' => array(
                'name' => 'predis',
                'options' => array(
                    'options' => array('prefix' => TEST_PREFIX),
                    'parameters' => $parameters,
                ),
            )));
            Di::getInstance()->setCache($cachePool);
        } catch (\Exception $e) {
            $this->assertTrue(false, "Error setting cache on Di".  $e);
        }

        $this->assertTrue(true);
    }

    /**
     * @depends testDiLog
     * @depends testDiCache
     */
    public function testSplitCacheInterface()
    {
        $splitChanges = file_get_contents(__DIR__."/../../files/splitChanges.json");
        $this->assertJson($splitChanges);

        Utils\Utils::addSplitsInCache($splitChanges);
        $splitCache = new SplitCache();

        $splitChanges = json_decode($splitChanges, true);
        $splits = $splitChanges['splits'];
        $split = $splits[0];
        $splitName = $split['name'];
        $flagSets = array('set_a', 'set_b');

        $expected = array();
        $expected['set_a'] = array($splits[1]['name'], $splitName);
        $expected['set_b'] = array($splits[1]['name'], $splitName);

        $this->assertEquals(strlen(json_encode($split)), strlen($splitCache->getSplit($splitName)));
        $this->assertEquals($splitChanges['till'], $splitCache->getChangeNumber());
        $this->assertEquals($expected, $splitCache->getNamesByFlagSets($flagSets));
    }

    /**
     * @depends testSplitCacheInterface
     */
    public function testSegmentCacheInterface()
    {
        $segmentChanges = file_get_contents(__DIR__."/../../files/segmentEmployeesChanges.json");
        $this->assertJson($segmentChanges);

        Utils\Utils::addSegmentsInCache($segmentChanges);

        $segmentData = json_decode($segmentChanges, true);
        $segmentName = $segmentData['name'];
        $segmentCache = new SegmentCache();
        $this->assertTrue(boolval($segmentCache->isInSegment($segmentName, "fake_user_id_4")));
        $this->assertFalse(boolval($segmentCache->isInSegment($segmentName, "fake_user_id_4_")));

        $this->assertEquals($segmentData['till'], $segmentCache->getChangeNumber($segmentName));
    }

    /**
     * @depends testDiLog
     * @depends testDiCache
     */
    public function testEventsCache()
    {
        $key= "some_key";
        $trafficType = "some_trafficType";
        $eventType = "some_event_type";
        $value = 0.0;

        $eventDTO = new EventDTO($key, $trafficType, $eventType, $value, null);
        $eventQueueMessage = new EventQueueMessage(new QueueMetadataMessage(), $eventDTO);

        $this->assertTrue(EventsCache::addEvent($eventQueueMessage));
    }

    /**
     * @depends testDiLog
     * @depends testDiCache
     */
    public function testEventsCacheWithProperties()
    {
        $key= "some_key";
        $trafficType = "some_trafficType";
        $eventType = "some_event_type";
        $value = 0.0;
        $properties = array(
            "test1" => "test",
            "test2" => 1,
            "test3" => true,
            "test4" => null,
        );

        $eventDTO = new EventDTO($key, $trafficType, $eventType, $value, $properties);
        $eventQueueMessage = new EventQueueMessage(new QueueMetadataMessage(), $eventDTO);

        $this->assertTrue(EventsCache::addEvent($eventQueueMessage));

        $this->assertEquals($properties, $eventDTO->getProperties());
    }
}
