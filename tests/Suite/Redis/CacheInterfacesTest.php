<?php
namespace SplitIO\Test\Suite\Redis;

use SplitIO\Component\Cache\EventsCache;
use SplitIO\Component\Cache\Pool;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Common\Di;
use SplitIO\Component\Cache\BlockUntilReadyCache;
use SplitIO\Component\Log\Handler\Stdout;
use SplitIO\Component\Log\Logger;
use SplitIO\Component\Log\LogLevelEnum;
use SplitIO\Sdk\Events\EventDTO;
use SplitIO\Sdk\Events\EventQueueMessage;
use SplitIO\Sdk\QueueMetadataMessage;

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
            $cachePoolAdapter = array(
                'name' => 'redis',
                'options' => [
                    'client' => new \RedisMock,
                ]
            );

            $cachePool = new Pool(array( 'adapter' => $cachePoolAdapter ));
            Di::getInstance()->setCache($cachePool);
        } catch (\Exception $e) { throw $e;
            $this->assertTrue(false, "Error setting cache on Di");
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

        $splitCache = new SplitCache();

        $splitChanges = json_decode($splitChanges, true);
        $splits = $splitChanges['splits'];
        $split = $splits[0];

        $splitName = $split['name'];

        $this->assertTrue($splitCache->addSplit($splitName, json_encode($split)));

        $this->assertEquals(strlen(json_encode($split)), strlen($splitCache->getSplit($splitName)));

        $this->assertTrue($splitCache->removeSplit($splitName));

        $this->assertTrue($splitCache->setChangeNumber($splitChanges['till']));

        $this->assertEquals($splitChanges['till'], $splitCache->getChangeNumber());
    }

    /**
     * @depends testSplitCacheInterface
     */
    public function testSegmentCacheInterface()
    {
        $segmentChanges = file_get_contents(__DIR__."/../../files/segmentEmployeesChanges.json");

        $this->assertJson($segmentChanges);

        $segmentData = json_decode($segmentChanges, true);

        $segmentName = $segmentData['name'];

        $segmentCache = new SegmentCache();

        $this->assertArrayHasKey('fake_user_id_4', $segmentCache->addToSegment($segmentName, $segmentData['added']));

        $removedResult = $segmentCache->removeFromSegment($segmentName, $segmentData['removed']);
        $this->assertArrayHasKey('fake_user_id_6', $removedResult);

        $this->assertTrue($segmentCache->setChangeNumber($segmentName, $segmentData['till']));

        $this->assertEquals($segmentData['till'], $segmentCache->getChangeNumber($segmentName));
    }

    /**
     * @depends testDiLog
     * @depends testDiCache
     */
    public function testBlockUntilReadyCacheInterface()
    {
        $this->markTestIncomplete('Class BlockUntilReadyCache is not present. SplitFactory::doBUR is also not implemented!');
        $dateTimeUTC = new \DateTime("now", new \DateTimeZone("UTC"));
        $deltaTime = 100;

        $splitsTimestamp = $dateTimeUTC->getTimestamp();
        $segmentsTimestamp = $dateTimeUTC->getTimestamp() + $deltaTime;

        $bur = new BlockUntilReadyCache();
        $bur->setReadySplits($splitsTimestamp);
        $bur->setReadySegments($segmentsTimestamp);

        //Checking
        $this->assertEquals($splitsTimestamp, $bur->getReadySplits());
        $this->assertEquals($segmentsTimestamp, $bur->getReadySegments());

        $this->assertEquals(min($splitsTimestamp, $segmentsTimestamp), $bur->getReadyCheckpoint());
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
        $properties = [];

        $eventDTO = new EventDTO($key, $trafficType, $eventType, $value, $properties);
        $eventMessageMetadata = new QueueMetadataMessage();
        $eventQueueMessage = new EventQueueMessage($eventMessageMetadata, $eventDTO);

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
        $eventMessageMetadata = new QueueMetadataMessage();
        $eventQueueMessage = new EventQueueMessage($eventMessageMetadata, $eventDTO);

        $this->assertTrue(EventsCache::addEvent($eventQueueMessage));

        $this->assertEquals($properties, $eventDTO->getProperties());
    }
}
