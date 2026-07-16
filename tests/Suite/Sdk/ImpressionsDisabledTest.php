<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Component\Common\Di;
use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Test\Utils;
use SplitIO\Test\Suite\Sdk\Helpers\ImpressionsDisabledListener;

class ImpressionsDisabledTest extends \PHPUnit\Framework\TestCase
{
    private function createFactory($impressionListener = null)
    {
        $parameters = array(
            'scheme' => 'redis',
            'host' => REDIS_HOST,
            'port' => REDIS_PORT,
            'timeout' => 881,
        );
        $options = array();
        $options['cache'] = array(
            'adapter' => 'predis',
            'parameters' => $parameters,
            'options' => array('prefix' => TEST_PREFIX)
        );
        if ($impressionListener !== null) {
            $options['impressionListener'] = $impressionListener;
        }
        return \SplitIO\Sdk::factory('test-api-key', $options);
    }

    private function getRedisClient()
    {
        return new \Predis\Client(
            array(
                'host' => REDIS_HOST,
                'port' => REDIS_PORT,
            ),
            array('prefix' => TEST_PREFIX)
        );
    }

    private function seedSplitWithImpressionsDisabled($name, $impressionsDisabled, $killed = false, $sets = array())
    {
        $split = array(
            'name' => $name,
            'trafficTypeName' => 'user',
            'seed' => 123456789,
            'status' => 'ACTIVE',
            'killed' => $killed,
            'defaultTreatment' => 'off',
            'changeNumber' => 1750000000000,
            'algo' => 2,
            'sets' => $sets,
            'conditions' => array(
                array(
                    'matcherGroup' => array(
                        'combiner' => 'AND',
                        'matchers' => array(
                            array(
                                'matcherType' => 'ALL_KEYS',
                                'negate' => false,
                            )
                        )
                    ),
                    'partitions' => array(
                        array('treatment' => 'on', 'size' => 100),
                        array('treatment' => 'off', 'size' => 0)
                    )
                )
            )
        );

        if ($impressionsDisabled !== null) {
            $split['impressionsDisabled'] = $impressionsDisabled;
        }

        $splitChanges = json_encode(array('splits' => array($split), 'till' => 1750000000000));
        Utils\Utils::addSplitsInCache($splitChanges);
    }

    public function setUp(): void
    {
        Utils\Utils::cleanCache();
        Di::set(Di::KEY_FACTORY_TRACKER, false);
    }

    public function tearDown(): void
    {
        Utils\Utils::cleanCache();
    }

    public function testSingleEvalImpressionDisabled()
    {
        $this->seedSplitWithImpressionsDisabled('flag_disabled', true);
        $factory = $this->createFactory();
        $client = $factory->client();
        $redis = $this->getRedisClient();

        $treatment = $client->getTreatment('e2e_user_1', 'flag_disabled');
        $this->assertEquals('on', $treatment);

        // Should NOT be in Redis queue
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw);
    }

    public function testSingleEvalImpressionEnabled()
    {
        $this->seedSplitWithImpressionsDisabled('flag_enabled', false);
        $factory = $this->createFactory();
        $client = $factory->client();
        $redis = $this->getRedisClient();

        $treatment = $client->getTreatment('e2e_user_1', 'flag_enabled');
        $this->assertEquals('on', $treatment);

        // Should be in Redis queue
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);
        $this->assertEquals('flag_enabled', $parsed['i']['f']);
        $this->assertEquals('e2e_user_1', $parsed['i']['k']);
        $this->assertEquals('on', $parsed['i']['t']);
    }

    public function testPropertyAbsentDefaultsToEnabled()
    {
        $this->seedSplitWithImpressionsDisabled('flag_legacy', null);
        $factory = $this->createFactory();
        $client = $factory->client();
        $redis = $this->getRedisClient();

        $treatment = $client->getTreatment('e2e_user_1', 'flag_legacy');
        $this->assertEquals('on', $treatment);

        // Should be in Redis queue (default is tracked)
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);
        $this->assertEquals('flag_legacy', $parsed['i']['f']);
    }

    public function testNonBooleanJsonValueTruthy()
    {
        // Seed with numeric 1 (truthy)
        $redis = $this->getRedisClient();
        $split = array(
            'name' => 'flag_truthy',
            'trafficTypeName' => 'user',
            'seed' => 123456789,
            'status' => 'ACTIVE',
            'killed' => false,
            'defaultTreatment' => 'off',
            'changeNumber' => 1750000000000,
            'algo' => 2,
            'sets' => array(),
            'impressionsDisabled' => 1,
            'conditions' => array(
                array(
                    'matcherGroup' => array(
                        'combiner' => 'AND',
                        'matchers' => array(
                            array('matcherType' => 'ALL_KEYS', 'negate' => false)
                        )
                    ),
                    'partitions' => array(
                        array('treatment' => 'on', 'size' => 100),
                        array('treatment' => 'off', 'size' => 0)
                    )
                )
            )
        );
        $redis->set('SPLITIO.split.flag_truthy', json_encode($split));
        $redis->set('SPLITIO.splits.till', 1750000000000);

        $factory = $this->createFactory();
        $client = $factory->client();

        $treatment = $client->getTreatment('e2e_user_1', 'flag_truthy');
        $this->assertEquals('on', $treatment);

        // (bool)1 === true, so should NOT be queued
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw);
    }

    public function testMixedBatchGetTreatments()
    {
        $this->seedSplitWithImpressionsDisabled('flag_batch_disabled', true);
        $this->seedSplitWithImpressionsDisabled('flag_batch_enabled', false);
        $factory = $this->createFactory();
        $client = $factory->client();
        $redis = $this->getRedisClient();

        $treatments = $client->getTreatments('e2e_user_1', array('flag_batch_disabled', 'flag_batch_enabled'));
        $this->assertEquals('on', $treatments['flag_batch_disabled']);
        $this->assertEquals('on', $treatments['flag_batch_enabled']);

        // Only the enabled one should be queued
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);
        $this->assertEquals('flag_batch_enabled', $parsed['i']['f']);

        // No second impression
        $raw2 = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw2);
    }

    public function testMixedBatchGetTreatmentsWithConfig()
    {
        $this->seedSplitWithImpressionsDisabled('flag_config_disabled', true);
        $this->seedSplitWithImpressionsDisabled('flag_config_enabled', false);
        $factory = $this->createFactory();
        $client = $factory->client();
        $redis = $this->getRedisClient();

        $results = $client->getTreatmentsWithConfig('e2e_user_1', array('flag_config_disabled', 'flag_config_enabled'));
        $this->assertEquals('on', $results['flag_config_disabled']['treatment']);
        $this->assertEquals('on', $results['flag_config_enabled']['treatment']);

        // Only the enabled one should be queued
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);
        $this->assertEquals('flag_config_enabled', $parsed['i']['f']);

        $raw2 = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw2);
    }

    public function testDisabledAndKilled()
    {
        $this->seedSplitWithImpressionsDisabled('flag_disabled_killed', true, true);
        $factory = $this->createFactory();
        $client = $factory->client();
        $redis = $this->getRedisClient();

        $treatment = $client->getTreatment('e2e_user_1', 'flag_disabled_killed');
        // Killed returns defaultTreatment
        $this->assertEquals('off', $treatment);

        // Even though killed, impressionsDisabled=true means NOT queued
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw);
    }

    public function testManagerSplitDisabled()
    {
        $this->seedSplitWithImpressionsDisabled('flag_manager_disabled', true);
        $factory = $this->createFactory();
        $manager = $factory->manager();

        $splitView = $manager->split('flag_manager_disabled');
        $this->assertNotNull($splitView);
        $this->assertEquals('flag_manager_disabled', $splitView->getName());
        $this->assertTrue($splitView->getImpressionsDisabled());
    }

    public function testManagerSplitLegacy()
    {
        $this->seedSplitWithImpressionsDisabled('flag_manager_legacy', null);
        $factory = $this->createFactory();
        $manager = $factory->manager();

        $splitView = $manager->split('flag_manager_legacy');
        $this->assertNotNull($splitView);
        $this->assertEquals('flag_manager_legacy', $splitView->getName());
        $this->assertFalse($splitView->getImpressionsDisabled());
    }

    public function testMixedBatchGetTreatmentsByFlagSet()
    {
        $this->seedSplitWithImpressionsDisabled('flag_flagset_disabled', true, false, array('set_unit'));
        $this->seedSplitWithImpressionsDisabled('flag_flagset_enabled', false, false, array('set_unit'));
        $factory = $this->createFactory();
        $client = $factory->client();
        $redis = $this->getRedisClient();

        $treatments = $client->getTreatmentsByFlagSet('e2e_user_1', 'set_unit');
        $this->assertEquals('on', $treatments['flag_flagset_disabled']);
        $this->assertEquals('on', $treatments['flag_flagset_enabled']);

        // Only the enabled one should be queued
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);
        $this->assertEquals('flag_flagset_enabled', $parsed['i']['f']);

        // No second impression
        $raw2 = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw2);
    }

    public function testMixedBatchGetTreatmentsByFlagSets()
    {
        $this->seedSplitWithImpressionsDisabled('flag_flagsets_disabled', true, false, array('set_unit2'));
        $this->seedSplitWithImpressionsDisabled('flag_flagsets_enabled', false, false, array('set_unit2'));
        $factory = $this->createFactory();
        $client = $factory->client();
        $redis = $this->getRedisClient();

        $treatments = $client->getTreatmentsByFlagSets('e2e_user_1', array('set_unit2'));
        $this->assertEquals('on', $treatments['flag_flagsets_disabled']);
        $this->assertEquals('on', $treatments['flag_flagsets_enabled']);

        // Only the enabled one should be queued
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);
        $this->assertEquals('flag_flagsets_enabled', $parsed['i']['f']);

        // No second impression
        $raw2 = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw2);
    }

    public function testExceptionPathStillQueues()
    {
        // Induce evaluation exception by storing malformed JSON that causes json_decode
        // or Split constructor to fail. When Evaluator catches the exception,
        // Client::doEvaluation's outer catch builds a control impression with label EXCEPTION
        // and changeNumber -1. This impression MUST still be queued (wrapped as not-disabled).
        // This is a regression guard: exception queuing behavior is unchanged by impressionsDisabled.
        $redis = $this->getRedisClient();

        // Store invalid JSON (missing required fields) that will cause Split construction to fail
        $redis->set('SPLITIO.split.flag_exception', '{"name":"flag_exception"}');
        $redis->set('SPLITIO.splits.till', 1750000000000);

        $factory = $this->createFactory();
        $client = $factory->client();

        $treatment = $client->getTreatment('e2e_user_1', 'flag_exception');
        $this->assertEquals('control', $treatment);

        // The EXCEPTION impression MUST still be queued
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);
        $this->assertEquals('flag_exception', $parsed['i']['f']);
        $this->assertEquals('control', $parsed['i']['t']);
        $this->assertEquals('exception', $parsed['i']['r']);
    }

    public function testNonBooleanJsonValueNull()
    {
        // Non-boolean JSON value null must cast to false → impression IS logged.
        // (bool)null === false, so tracking is enabled (back-compat critical).
        // The existing helper omits null, so seed manually with explicit null.
        $redis = $this->getRedisClient();
        $split = array(
            'name' => 'flag_null',
            'trafficTypeName' => 'user',
            'seed' => 123456789,
            'status' => 'ACTIVE',
            'killed' => false,
            'defaultTreatment' => 'off',
            'changeNumber' => 1750000000000,
            'algo' => 2,
            'sets' => array(),
            'impressionsDisabled' => null,
            'conditions' => array(
                array(
                    'matcherGroup' => array(
                        'combiner' => 'AND',
                        'matchers' => array(
                            array('matcherType' => 'ALL_KEYS', 'negate' => false)
                        )
                    ),
                    'partitions' => array(
                        array('treatment' => 'on', 'size' => 100),
                        array('treatment' => 'off', 'size' => 0)
                    )
                )
            )
        );
        $redis->set('SPLITIO.split.flag_null', json_encode($split));
        $redis->set('SPLITIO.splits.till', 1750000000000);

        $factory = $this->createFactory();
        $client = $factory->client();

        $treatment = $client->getTreatment('e2e_user_1', 'flag_null');
        $this->assertEquals('on', $treatment);

        // Should be queued: (bool)null === false → tracked
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);
        $this->assertEquals('flag_null', $parsed['i']['f']);
    }

    public function testNonBooleanJsonValueStringTrue()
    {
        // Non-boolean JSON string "true" casts truthy → NOT queued.
        // (bool)"true" === true in PHP (any non-empty string is truthy).
        $redis = $this->getRedisClient();
        $split = array(
            'name' => 'flag_string_true',
            'trafficTypeName' => 'user',
            'seed' => 123456789,
            'status' => 'ACTIVE',
            'killed' => false,
            'defaultTreatment' => 'off',
            'changeNumber' => 1750000000000,
            'algo' => 2,
            'sets' => array(),
            'impressionsDisabled' => 'true',
            'conditions' => array(
                array(
                    'matcherGroup' => array(
                        'combiner' => 'AND',
                        'matchers' => array(
                            array('matcherType' => 'ALL_KEYS', 'negate' => false)
                        )
                    ),
                    'partitions' => array(
                        array('treatment' => 'on', 'size' => 100),
                        array('treatment' => 'off', 'size' => 0)
                    )
                )
            )
        );
        $redis->set('SPLITIO.split.flag_string_true', json_encode($split));
        $redis->set('SPLITIO.splits.till', 1750000000000);

        $factory = $this->createFactory();
        $client = $factory->client();

        $treatment = $client->getTreatment('e2e_user_1', 'flag_string_true');
        $this->assertEquals('on', $treatment);

        // Should NOT be queued: (bool)"true" === true
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw);
    }

    public function testListenerDivergenceUnit()
    {
        // Listener divergence guard: disabled + listener → listener fires with full payload;
        // enabled + listener → listener fires AND queue receives. This tests both directions.
        $listener = new ImpressionsDisabledListener();
        $this->seedSplitWithImpressionsDisabled('flag_listener_disabled', true);
        $this->seedSplitWithImpressionsDisabled('flag_listener_enabled', false);
        $factory = $this->createFactory($listener);
        $client = $factory->client();
        $redis = $this->getRedisClient();

        // Call getTreatment on each
        $treatment1 = $client->getTreatment('e2e_user_1', 'flag_listener_disabled');
        $treatment2 = $client->getTreatment('e2e_user_1', 'flag_listener_enabled');
        $this->assertEquals('on', $treatment1);
        $this->assertEquals('on', $treatment2);

        // Listener should receive BOTH (disabled fires listener)
        $this->assertCount(2, $listener->receivedImpressions);

        // Redis queue should contain ONLY the enabled one
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);
        $this->assertEquals('flag_listener_enabled', $parsed['i']['f']);

        // No second impression in queue
        $raw2 = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw2);
    }

    public function testCrossSdkParityCanonicalFixture()
    {
        // Cross-SDK parity test using canonical fixture from official Split spec.
        // Canonical spec fixture uses flag names "tracked" (impressionsDisabled: false)
        // and "not_tracked" (impressionsDisabled: true). The spec's canonical conditions
        // use an IN_SEGMENT matcher (segment "new_segment") with partitions putting 100% on
        // treatment "free". Reproducing that verbatim would require seeding a segment and
        // would make the test about segment-matching, not impression-toggling. Per the
        // standard fixture-reconciliation rule (repo conventions win for structure; only
        // the impressionsDisabled lines are authoritative from the spec), we substitute
        // an ALL_KEYS matcher with partitions on:100 / off:0, defaultTreatment off,
        // like the other tests in this file. This keeps the test deterministic without
        // segment seeding while preserving the parity-relevant flag names and
        // impressionsDisabled values from the spec.
        $this->seedSplitWithImpressionsDisabled('tracked', false);
        $this->seedSplitWithImpressionsDisabled('not_tracked', true);

        // Redis analog surface (spec tests #2-#4 reduced to drop-silently):
        // In the spec's other SDKs, tests #2-#4 verify mode-based POST behavior
        // (None/Debug/Optimized). This SDK is Redis-consumer-only with no HTTP
        // impression recorder, no mode machinery, no uniquekeys/impressionsCount.
        // The parity analog for this SDK is: both flags evaluate correctly, only
        // "tracked" lands in Redis SPLITIO.impressions queue, "not_tracked" is
        // dropped silently, and the listener (if attached) receives both.
        $listener = new ImpressionsDisabledListener();
        $factory = $this->createFactory($listener);
        $client = $factory->client();
        $redis = $this->getRedisClient();

        // Use eval key "CUSTOMER_ID" to echo the spec's canonical example
        $trackedTreatment = $client->getTreatment('CUSTOMER_ID', 'tracked');
        $notTrackedTreatment = $client->getTreatment('CUSTOMER_ID', 'not_tracked');

        // Both treatments should be "on" (ALL_KEYS matcher with on:100 partition)
        $this->assertEquals('on', $trackedTreatment);
        $this->assertEquals('on', $notTrackedTreatment);

        // Listener should receive BOTH (drop-silently still fires listener)
        $this->assertCount(2, $listener->receivedImpressions);

        // Redis queue should contain ONLY "tracked" impression
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);
        $this->assertEquals('tracked', $parsed['i']['f']);
        $this->assertEquals('CUSTOMER_ID', $parsed['i']['k']);
        $this->assertEquals('on', $parsed['i']['t']);

        // "not_tracked" impression should NOT be in queue
        $raw2 = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw2);

        // Manager surface (spec test #1): verify impressionsDisabled property exposure
        $manager = $factory->manager();

        $trackedSplit = $manager->split('tracked');
        $this->assertNotNull($trackedSplit);
        $this->assertEquals('tracked', $trackedSplit->getName());
        $this->assertFalse($trackedSplit->getImpressionsDisabled());

        $notTrackedSplit = $manager->split('not_tracked');
        $this->assertNotNull($notTrackedSplit);
        $this->assertEquals('not_tracked', $notTrackedSplit->getName());
        $this->assertTrue($notTrackedSplit->getImpressionsDisabled());
    }
}
