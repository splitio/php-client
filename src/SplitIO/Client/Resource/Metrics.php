<?php
namespace SplitIO\Client\Resource;

use SplitIO\Cache\MetricsCache;
use SplitIO\Client\ClientBase;
use SplitIO\Http\Client as HttpClient;
use SplitIO\Http\MethodEnum;
use SplitIO\Split as SplitApp;
use SplitIO\Metrics as MetricsModule;

class Metrics extends ClientBase
{
    private $servicePath = '/api/metrics/times';

    public function sendMetrics()
    {
        $metricsKeys = SplitApp::cache()->getKeys(MetricsCache::getCacheKeySearchLatencyPattern());
        $metricsCache = new MetricsCache();

        $cachedMetrics = [];

        foreach ($metricsKeys as $key) {

            $metricName = MetricsCache::getMetricNameFromKey($key);
            $metricBucket = MetricsCache::getBucketFromKey($key);

            if (!isset($cachedMetrics[$metricName]) || !is_array($cachedMetrics[$metricName])) {
                //initialization latency butckets
                $maxBucket = MetricsModule::getBucketForLatencyMicros(MetricsModule::MAX_LATENCY);
                $cachedMetrics[$metricName] = array_fill(0, $maxBucket + 1, 0);
            }

            $cachedMetrics[$metricName][$metricBucket] = $metricsCache->getLatencyAndReset($key);
        }

        $dataset = [];

        foreach ($cachedMetrics as $name => $latencies) {
            $dataset[] = ['name' => $name, 'latencies' => $latencies];
        }

        //Sending Metrics dataset.
        $httpClient = new HttpClient();
        $request = $this->getRequest(MethodEnum::POST(), $this->servicePath);
        $request->setData($dataset);
        $request->setHeader("Content-Type", "application/json");

        $response = $httpClient->send($request);

        if ($response->isSuccess()) {
            SplitApp::logger()->info(count($dataset) . " Metrics sent successfuly");
        } else {
            SplitApp::logger()->error("Metrics have not been sent successfully");
            SplitApp::logger()->error("HTTP Code: ".$response->getStatusCode());
            SplitApp::logger()->error("HTTP Body: ".$response->getBody());
        }

    }
}
