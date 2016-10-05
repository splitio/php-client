<?php
namespace SplitIO\Test\Suite\Redis;

use SplitIO\Component\Cache\Pool;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Common\Di;
use SplitIO\Component\Cache\BlockUntilReadyCache;
use SplitIO\Component\Log\Handler\Stdout;
use SplitIO\Component\Log\Logger;
use SplitIO\Component\Log\LogLevelEnum;

class CacheInterfacesTest extends \PHPUnit_Framework_TestCase
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
            $cachePoolAdapter = [
                'name' => 'redis',
                'options' => [
                    'host' => REDIS_HOST,
                    'port' => REDIS_PORT,
                ]
            ];

            $cachePool = new Pool([ 'adapter' => $cachePoolAdapter ]);
            Di::getInstance()->setCache($cachePool);

        } catch (\Exception $e) {
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
}
