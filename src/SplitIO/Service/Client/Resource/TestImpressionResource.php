<?php
namespace SplitIO\Service\Client\Resource;

use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Service\Client\ClientBase;
use GuzzleHttp\Client as HttpClient;
use SplitIO\Component\Http\MethodEnum;
use SplitIO\Component\Http\ResponseHelper;
use SplitIO\Component\Common\Di;

class TestImpressionResource extends EventTypeResource
{
    private $servicePath = '/api/testImpressions/bulk';

    public function sendTestImpressions()
    {
        $impressionKeys = Di::getCache()->getKeys(ImpressionCache::getCacheKeySearchPattern());
        $impressionCache = new ImpressionCache();

        $dataset = array();

        foreach ($impressionKeys as $key) {

            $featureName = ImpressionCache::getFeatureNameFromKey($key);
            $cachedImpressions = $impressionCache->getAllImpressions($key);
            $impressions = array();

            for ($i=0; $i < count($cachedImpressions); $i++) {
                //restoring cached impressions from JSON string to PHP Array.
                $impressions[$i] = json_decode($cachedImpressions[$i], true);
            }

            //Bulk data set
            $dataset[] = array('testName' => $featureName, 'keyImpressions' => $impressions);

        }

        //Sending Impressions dataset.
        $response = $this->post($this->servicePath, $dataset);

        if (ResponseHelper::isSuccessful($response->getStatusCode())) {
            Di::getLogger()->info(count($dataset)." Impressions sent successfuly");
            $this->dropDataset($dataset);
            return true;
        }

        $this->dropDataset($dataset);
        return false;
    }

    private function dropDataset($dataset)
    {
        $impressionCache = new ImpressionCache();

        try {
            //removing sent impressions from cache.
            foreach ($dataset as $tiDTO) {
                $rKey = ImpressionCache::getCacheKeyForImpressionData($tiDTO['testName']);

                foreach ($tiDTO['keyImpressions'] as $imp) {
                    $impressionCache->removeImpression($rKey, json_encode($imp));
                }
            }
            Di::getLogger()->info("Sent Impressions removed from cache successfuly");
        } catch (\Exception $e) {
            Di::getLogger()->error($e->getMessage());
        }
    }
}
