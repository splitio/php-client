<?php
namespace SplitIO\Test\Suite\Metrics;

use SplitIO\Metrics as MetricsModule;

class MetricsTest extends \PHPUnit\Framework\TestCase
{

    private $latencies_limits = array(1000, 1500, 2250, 3375, 5063, 7594, 11391, 17086, 25629, 38443,
        57665, 86498, 129746, 194620, 291929, 437894, 656841, 985261, 1477892, 2216838,
        3325257, 4987885, 7481828);

    private $latencies = array(800, 1220, 1765, 2251, 4021, 6093, 10212, 17000, 24000, 38000,
        42000, 85432, 128123, 194000, 290029, 427000, 646123, 984261, 1477892, 2216838,
        3325257, 4987885, 7481828);

    /**
     * Latencies of <= 1000 micros correspond to the first bucket (index 0)
     */
    public function testLessThanFirstBucket()
    {
        $this->assertEquals(0, MetricsModule::getBucketForLatencyMicros(0));
        $this->assertEquals(0, MetricsModule::getBucketForLatencyMicros(750));
        $this->assertEquals(0, MetricsModule::getBucketForLatencyMicros(450));
    }

    /**
     * Latencies of 1 millis or <= 1000 micros correspond to the first bucket (index 0)
     */
    public function testFirstBucket()
    {
        $this->assertEquals(0, MetricsModule::getBucketForLatencyMicros(1000));
    }


    /**
     * Latencies of 7481 millis or 7481828 micros correspond to the last bucket (index 22)
     */

    public function testLastBucket()
    {
        $this->assertEquals(22, MetricsModule::getBucketForLatencyMicros(7481828));
    }

    /**
     * Latencies of more than 7481 millis or 7481828 micros correspond to the last bucket (index 22)
     */
    public function testGreaterThanLastBucket()
    {
        $this->assertEquals(22, MetricsModule::getBucketForLatencyMicros(7481830));
        $this->assertEquals(22, MetricsModule::getBucketForLatencyMicros(7999999));
    }

    /**
     * Latencies between 11,392 and 17,086 are in the 8th bucket.
     */
    public function test8ThBucket()
    {
        $this->assertEquals(7, MetricsModule::getBucketForLatencyMicros(11392));
        $this->assertEquals(7, MetricsModule::getBucketForLatencyMicros(17086));
    }

    public function testBucketSelection()
    {
        foreach ($this->latencies as $bucket => $latency) {
            $this->assertEquals($bucket, MetricsModule::getBucketForLatencyMicros($latency));
        }

        foreach ($this->latencies_limits as $bucket => $latency) {
            $this->assertEquals($bucket, MetricsModule::getBucketForLatencyMicros($latency));
        }
    }
}
