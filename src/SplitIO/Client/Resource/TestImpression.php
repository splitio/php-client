<?php
namespace SplitIO\Client\Resource;

use SplitIO\Cache\ImpressionCache;
use SplitIO\Client\ClientBase;
use SplitIO\Http\Client as HttpClient;
use SplitIO\Http\MethodEnum;
use SplitIO\Split as SplitApp;

class TestImpression extends ClientBase
{
    private $servicePath = '/api/testImpressions/bulk';

    public function sendTestImpressions()
    {
        $impressionKeys = SplitApp::cache()->getKeys(ImpressionCache::getCacheKeySearchPattern());
        $impressionCache = new ImpressionCache();

        $dataset = [];

        foreach ($impressionKeys as $key) {

            $featureName = ImpressionCache::getFeatureNameFromKey($key);
            $cachedImpressions = $impressionCache->getAllImpressions($key);
            $impressions = [];

            for ($i=0; $i < count($cachedImpressions); $i++) {
                //restoring cached impressions from JSON string to PHP Array.
                $impressions[$i] = json_decode($cachedImpressions[$i], true);
            }

            //Bulk data set
            $dataset[] = ['testName' => $featureName, 'keyImpressions' => $impressions];

        }

        //Sending Impressions dataset.
        $httpClient = new HttpClient();
        $request = $this->getRequest(MethodEnum::POST(), $this->servicePath);
        $request->setData($dataset);
        $request->setHeader("Content-Type", "application/json");

        $response = $httpClient->send($request);

        if ($response->isSuccess()) {
            SplitApp::logger()->info(count($dataset)." Impressions sent successfuly");

            try {
                //removing sent impressions from cache.
                foreach ($dataset as $tiDTO) {
                    $rKey = ImpressionCache::getCacheKeyForImpressionData($tiDTO['testName']);

                    foreach ($tiDTO['keyImpressions'] as $imp) {
                        $impressionCache->removeImpression($rKey, json_encode($imp));
                    }
                }
                SplitApp::logger()->info("Sent Impressions removed from cache successfuly");
            } catch (\Exception $e) {
                SplitApp::logger()->error($e->getMessage());
            }

            return true;
        }

        return false;
    }
}
