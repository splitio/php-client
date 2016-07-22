<?php
namespace SplitIO\Service\Client\Resource;

use SplitIO\Component\Cache\MetricsCache;
use SplitIO\Component\Http\ResponseHelper;
use SplitIO\Component\Stats\Latency;
use SplitIO\Service\Client\ClientBase;
use SplitIO\Component\Common\Di;

class MetricsResource extends EventTypeResource
{
    private $servicePath = '/api/metrics/times';

    public function sendMetrics()
    {
        $metricsKeys = Di::getCache()->getKeys(MetricsCache::getCacheKeySearchLatencyPattern());
        $metricsCache = new MetricsCache();

        $cachedMetrics = array();

        foreach ($metricsKeys as $key) {

            $metricName = MetricsCache::getMetricNameFromKey($key);
            $metricBucket = MetricsCache::getBucketFromKey($key);

            if (!isset($cachedMetrics[$metricName]) || !is_array($cachedMetrics[$metricName])) {
                //initialization latency butckets
                $maxBucket = Latency::getBucketForLatencyMicros(Latency::MAX_LATENCY);
                $cachedMetrics[$metricName] = array_fill(0, $maxBucket + 1, 0);
            }

            $cachedMetrics[$metricName][$metricBucket] = $metricsCache->getLatencyAndReset($key);
        }

        $dataset = array();

        foreach ($cachedMetrics as $name => $latencies) {
            $dataset[] = array('name' => $name, 'latencies' => $latencies);
        }

        //Sending Metrics dataset.
        $response = $this->post($this->servicePath, $dataset);

        if (ResponseHelper::isSuccessful($response->getStatusCode())) {
            Di::getLogger()->info(count($dataset) . " Metrics sent successfuly");
        } else {
            Di::getLogger()->error("Metrics have not been sent successfully");
            Di::getLogger()->error("HTTP Code: ".$response->getStatusCode());
            Di::getLogger()->error("HTTP Body: ".$response->getBody());
        }

    }
}
