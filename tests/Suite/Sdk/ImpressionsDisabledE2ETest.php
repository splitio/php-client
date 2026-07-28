<?php
namespace SplitIO\Test\Suite\Sdk;

use SplitIO\Component\Common\Di;
use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Test\Utils;
use SplitIO\Test\Suite\Sdk\Helpers\ImpressionsDisabledE2EListener;

class ImpressionsDisabledE2ETest extends \PHPUnit\Framework\TestCase
{
    private function createFactory($listener = null)
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
        if ($listener !== null) {
            $options['impressionListener'] = $listener;
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

    private function seedE2EFixtures()
    {
        $redis = $this->getRedisClient();

        // Fixture 1: disabled, in set_e2e
        $split1 = array(
            'name' => 'flag_e2e_disabled',
            'trafficTypeName' => 'user',
            'seed' => 123456789,
            'status' => 'ACTIVE',
            'killed' => false,
            'defaultTreatment' => 'off',
            'changeNumber' => 1750000000000,
            'algo' => 2,
            'sets' => array('set_e2e'),
            'impressionsDisabled' => true,
            'conditions' => array(
                array(
                    'matcherGroup' => array(
                        'combiner' => 'AND',
                        'matchers' => array(array('matcherType' => 'ALL_KEYS', 'negate' => false))
                    ),
                    'partitions' => array(
                        array('treatment' => 'on', 'size' => 100),
                        array('treatment' => 'off', 'size' => 0)
                    )
                )
            )
        );

        // Fixture 2: enabled, in set_e2e
        $split2 = array(
            'name' => 'flag_e2e_enabled',
            'trafficTypeName' => 'user',
            'seed' => 123456789,
            'status' => 'ACTIVE',
            'killed' => false,
            'defaultTreatment' => 'off',
            'changeNumber' => 1750000000000,
            'algo' => 2,
            'sets' => array('set_e2e'),
            'impressionsDisabled' => false,
            'conditions' => array(
                array(
                    'matcherGroup' => array(
                        'combiner' => 'AND',
                        'matchers' => array(array('matcherType' => 'ALL_KEYS', 'negate' => false))
                    ),
                    'partitions' => array(
                        array('treatment' => 'on', 'size' => 100),
                        array('treatment' => 'off', 'size' => 0)
                    )
                )
            )
        );

        // Fixture 3: legacy (no property)
        $split3 = array(
            'name' => 'flag_e2e_legacy',
            'trafficTypeName' => 'user',
            'seed' => 123456789,
            'status' => 'ACTIVE',
            'killed' => false,
            'defaultTreatment' => 'off',
            'changeNumber' => 1750000000000,
            'algo' => 2,
            'sets' => array(),
            'conditions' => array(
                array(
                    'matcherGroup' => array(
                        'combiner' => 'AND',
                        'matchers' => array(array('matcherType' => 'ALL_KEYS', 'negate' => false))
                    ),
                    'partitions' => array(
                        array('treatment' => 'on', 'size' => 100),
                        array('treatment' => 'off', 'size' => 0)
                    )
                )
            )
        );

        // Fixture 4: disabled + killed
        $split4 = array(
            'name' => 'flag_e2e_disabled_killed',
            'trafficTypeName' => 'user',
            'seed' => 123456789,
            'status' => 'ACTIVE',
            'killed' => true,
            'defaultTreatment' => 'off',
            'changeNumber' => 1750000000000,
            'algo' => 2,
            'sets' => array(),
            'impressionsDisabled' => true,
            'conditions' => array(
                array(
                    'matcherGroup' => array(
                        'combiner' => 'AND',
                        'matchers' => array(array('matcherType' => 'ALL_KEYS', 'negate' => false))
                    ),
                    'partitions' => array(
                        array('treatment' => 'on', 'size' => 100),
                        array('treatment' => 'off', 'size' => 0)
                    )
                )
            )
        );

        // Fixture 5: impressionsDisabled is numeric 1 (not boolean true) -> tracked
        $split5 = array(
            'name' => 'flag_e2e_truthy',
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
                        'matchers' => array(array('matcherType' => 'ALL_KEYS', 'negate' => false))
                    ),
                    'partitions' => array(
                        array('treatment' => 'on', 'size' => 100),
                        array('treatment' => 'off', 'size' => 0)
                    )
                )
            )
        );

        // Seed all splits
        $redis->set('SPLITIO.split.flag_e2e_disabled', json_encode($split1));
        $redis->set('SPLITIO.split.flag_e2e_enabled', json_encode($split2));
        $redis->set('SPLITIO.split.flag_e2e_legacy', json_encode($split3));
        $redis->set('SPLITIO.split.flag_e2e_disabled_killed', json_encode($split4));
        $redis->set('SPLITIO.split.flag_e2e_truthy', json_encode($split5));
        $redis->set('SPLITIO.splits.till', 1750000000000);

        // Seed flag-set index (set_e2e contains fixtures 1 and 2)
        $redis->sadd('SPLITIO.flagSet.set_e2e', 'flag_e2e_disabled');
        $redis->sadd('SPLITIO.flagSet.set_e2e', 'flag_e2e_enabled');
    }

    public function setUp(): void
    {
        Utils\Utils::cleanCache();
        Di::set(Di::KEY_FACTORY_TRACKER, false);
        $this->seedE2EFixtures();
    }

    public function tearDown(): void
    {
        Utils\Utils::cleanCache();
    }

    public function testE2EAllFixtures()
    {
        $listener = new ImpressionsDisabledE2EListener();
        $factory = $this->createFactory($listener);
        $client = $factory->client();
        $redis = $this->getRedisClient();

        // Exercise all fixtures via getTreatments
        $treatments = $client->getTreatments('e2e_user_1', array(
            'flag_e2e_disabled',
            'flag_e2e_enabled',
            'flag_e2e_legacy',
            'flag_e2e_disabled_killed',
            'flag_e2e_truthy'
        ));

        // All treatments correct
        $this->assertEquals('on', $treatments['flag_e2e_disabled']);
        $this->assertEquals('on', $treatments['flag_e2e_enabled']);
        $this->assertEquals('on', $treatments['flag_e2e_legacy']);
        $this->assertEquals('off', $treatments['flag_e2e_disabled_killed']); // killed
        $this->assertEquals('on', $treatments['flag_e2e_truthy']);

        // Redis: only flags that are NOT explicitly disabled are queued.
        // flag_e2e_truthy has impressionsDisabled=1 (not boolean true), so it IS tracked.
        $queuedFeatures = array();
        while ($raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY)) {
            $parsed = json_decode($raw, true);
            $queuedFeatures[] = $parsed['i']['f'];
        }
        $this->assertCount(3, $queuedFeatures);
        $this->assertContains('flag_e2e_enabled', $queuedFeatures);
        $this->assertContains('flag_e2e_legacy', $queuedFeatures);
        $this->assertContains('flag_e2e_truthy', $queuedFeatures);
        $this->assertNotContains('flag_e2e_disabled', $queuedFeatures);
        $this->assertNotContains('flag_e2e_disabled_killed', $queuedFeatures);

        // Listener: all 5 should be sent
        $this->assertCount(5, $listener->receivedImpressions);
        $listenerFeatures = array_map(function ($imp) {
            return $imp['feature'];
        }, $listener->receivedImpressions);
        $this->assertContains('flag_e2e_disabled', $listenerFeatures);
        $this->assertContains('flag_e2e_enabled', $listenerFeatures);
        $this->assertContains('flag_e2e_legacy', $listenerFeatures);
        $this->assertContains('flag_e2e_disabled_killed', $listenerFeatures);
        $this->assertContains('flag_e2e_truthy', $listenerFeatures);
    }

    public function testE2EGetTreatmentWithConfigDisabled()
    {
        $listener = new ImpressionsDisabledE2EListener();
        $factory = $this->createFactory($listener);
        $client = $factory->client();
        $redis = $this->getRedisClient();

        $result = $client->getTreatmentWithConfig('e2e_user_1', 'flag_e2e_disabled');
        $this->assertEquals('on', $result['treatment']);

        // Not in Redis
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNull($raw);

        // In listener
        $this->assertCount(1, $listener->receivedImpressions);
        $this->assertEquals('flag_e2e_disabled', $listener->receivedImpressions[0]['feature']);
    }

    public function testE2EGetTreatmentsWithConfigMixed()
    {
        $listener = new ImpressionsDisabledE2EListener();
        $factory = $this->createFactory($listener);
        $client = $factory->client();
        $redis = $this->getRedisClient();

        $results = $client->getTreatmentsWithConfig('e2e_user_1', array('flag_e2e_disabled', 'flag_e2e_enabled'));
        $this->assertEquals('on', $results['flag_e2e_disabled']['treatment']);
        $this->assertEquals('on', $results['flag_e2e_enabled']['treatment']);

        // Only enabled queued
        $queuedFeatures = array();
        while ($raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY)) {
            $parsed = json_decode($raw, true);
            $queuedFeatures[] = $parsed['i']['f'];
        }
        $this->assertCount(1, $queuedFeatures);
        $this->assertContains('flag_e2e_enabled', $queuedFeatures);

        // Both in listener
        $this->assertCount(2, $listener->receivedImpressions);
        $listenerFeatures = array_map(function ($imp) {
            return $imp['feature'];
        }, $listener->receivedImpressions);
        $this->assertContains('flag_e2e_disabled', $listenerFeatures);
        $this->assertContains('flag_e2e_enabled', $listenerFeatures);
    }

    public function testE2EGetTreatmentsByFlagSet()
    {
        $listener = new ImpressionsDisabledE2EListener();
        $factory = $this->createFactory($listener);
        $client = $factory->client();
        $redis = $this->getRedisClient();

        // set_e2e contains flag_e2e_disabled (disabled) + flag_e2e_enabled (enabled)
        $treatments = $client->getTreatmentsByFlagSet('e2e_user_1', 'set_e2e');

        // Both treatments correct
        $this->assertEquals('on', $treatments['flag_e2e_disabled']);
        $this->assertEquals('on', $treatments['flag_e2e_enabled']);

        // Only enabled queued
        $queuedFeatures = array();
        while ($raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY)) {
            $parsed = json_decode($raw, true);
            $queuedFeatures[] = $parsed['i']['f'];
        }
        $this->assertCount(1, $queuedFeatures);
        $this->assertContains('flag_e2e_enabled', $queuedFeatures);

        // Both in listener
        $this->assertCount(2, $listener->receivedImpressions);
        $listenerFeatures = array_map(function ($imp) {
            return $imp['feature'];
        }, $listener->receivedImpressions);
        $this->assertContains('flag_e2e_disabled', $listenerFeatures);
        $this->assertContains('flag_e2e_enabled', $listenerFeatures);
    }

    public function testE2EGetTreatmentsByFlagSets()
    {
        $listener = new ImpressionsDisabledE2EListener();
        $factory = $this->createFactory($listener);
        $client = $factory->client();
        $redis = $this->getRedisClient();

        // set_e2e contains flag_e2e_disabled (disabled) + flag_e2e_enabled (enabled)
        $treatments = $client->getTreatmentsByFlagSets('e2e_user_1', array('set_e2e'));

        // Both treatments correct
        $this->assertEquals('on', $treatments['flag_e2e_disabled']);
        $this->assertEquals('on', $treatments['flag_e2e_enabled']);

        // Only enabled queued
        $queuedFeatures = array();
        while ($raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY)) {
            $parsed = json_decode($raw, true);
            $queuedFeatures[] = $parsed['i']['f'];
        }
        $this->assertCount(1, $queuedFeatures);
        $this->assertContains('flag_e2e_enabled', $queuedFeatures);

        // Both in listener
        $this->assertCount(2, $listener->receivedImpressions);
        $listenerFeatures = array_map(function ($imp) {
            return $imp['feature'];
        }, $listener->receivedImpressions);
        $this->assertContains('flag_e2e_disabled', $listenerFeatures);
        $this->assertContains('flag_e2e_enabled', $listenerFeatures);
    }

    public function testE2EGetTreatmentsWithConfigByFlagSets()
    {
        $listener = new ImpressionsDisabledE2EListener();
        $factory = $this->createFactory($listener);
        $client = $factory->client();
        $redis = $this->getRedisClient();

        // set_e2e contains flag_e2e_disabled (disabled) + flag_e2e_enabled (enabled)
        $results = $client->getTreatmentsWithConfigByFlagSets('e2e_user_1', array('set_e2e'));

        // Both treatments correct
        $this->assertEquals('on', $results['flag_e2e_disabled']['treatment']);
        $this->assertEquals('on', $results['flag_e2e_enabled']['treatment']);

        // Only enabled queued
        $queuedFeatures = array();
        while ($raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY)) {
            $parsed = json_decode($raw, true);
            $queuedFeatures[] = $parsed['i']['f'];
        }
        $this->assertCount(1, $queuedFeatures);
        $this->assertContains('flag_e2e_enabled', $queuedFeatures);

        // Both in listener
        $this->assertCount(2, $listener->receivedImpressions);
        $listenerFeatures = array_map(function ($imp) {
            return $imp['feature'];
        }, $listener->receivedImpressions);
        $this->assertContains('flag_e2e_disabled', $listenerFeatures);
        $this->assertContains('flag_e2e_enabled', $listenerFeatures);
    }

    public function testE2EEnabledImpressionPayloadFidelity()
    {
        // Assert every field in the serialized impression payload for an enabled
        // flag, plus that the queue TTL is unchanged.
        $factory = $this->createFactory();
        $client = $factory->client();
        $redis = $this->getRedisClient();

        // Check TTL BEFORE rpop (rpop drains the list, leaving no TTL on empty list)
        $treatment = $client->getTreatment('e2e_user_1', 'flag_e2e_enabled');
        $this->assertEquals('on', $treatment);

        // Assert TTL is within bounds (> 0 and <= 3600)
        $ttl = $redis->ttl(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertGreaterThan(0, $ttl);
        $this->assertLessThanOrEqual(3600, $ttl);

        // Now rpop and assert full payload
        $raw = $redis->rpop(ImpressionCache::IMPRESSIONS_QUEUE_KEY);
        $this->assertNotNull($raw);
        $parsed = json_decode($raw, true);

        // Assert every impression field
        $this->assertEquals('e2e_user_1', $parsed['i']['k']);  // key
        $this->assertNull($parsed['i']['b']);  // bucketing key (null when simple string key used)
        $this->assertEquals('flag_e2e_enabled', $parsed['i']['f']);  // feature
        $this->assertEquals('on', $parsed['i']['t']);  // treatment
        $this->assertArrayHasKey('r', $parsed['i']);  // label field exists (may be null if labels disabled)
        $this->assertEquals(1750000000000, $parsed['i']['c']);  // changeNumber
        $this->assertIsInt($parsed['i']['m']);  // timestamp
        $this->assertGreaterThan(0, $parsed['i']['m']);

        // Assert metadata exists
        $this->assertArrayHasKey('m', $parsed);
        $this->assertIsArray($parsed['m']);
    }
}
