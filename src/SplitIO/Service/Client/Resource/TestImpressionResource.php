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

    public function sendTestImpressions($keyImpressionsPerTest)
    {
        $impressionKeys = Di::getCache()->getKeys(ImpressionCache::getCacheKeySearchPattern());
        $impressionCache = new ImpressionCache();

        $toDrop = array();
        $dataset = array();
        $totalImpressions = 0;

        foreach ($impressionKeys as $key) {
            $featureName = ImpressionCache::getFeatureNameFromKey($key);
            $cachedImpressions = $impressionCache->getRandomImpressions($key, $keyImpressionsPerTest);

            if (empty($cachedImpressions)) {
                continue;
            }

            $toDrop[$key] = $cachedImpressions;
            $impressions = array();

            $cachedImpressionsCount = count($cachedImpressions);
            $totalImpressions += $cachedImpressionsCount;
            for ($i=0; $i < $cachedImpressionsCount; $i++) {
                //restoring cached impressions from JSON string to PHP Array.
                $impressions[$i] = json_decode($cachedImpressions[$i], true);
            }

            //Bulk data set
            $dataset[] = array('testName' => $featureName, 'keyImpressions' => $impressions);
        }

        //Sending Impressions dataset.
        $response = $this->post($this->servicePath, $dataset);

        //Dropping impressions
        $this->dropDataset($toDrop);

        if ($response->isSuccessful()) {
            Di::getLogger()->info($totalImpressions." Impressions sent successfuly");
            return true;
        }

        return false;
    }

    private function dropDataset($toDrop)
    {
        try {
            $impressionCache = new ImpressionCache();

            foreach ($toDrop as $key => $impressions) {
                Di::getLogger()->debug("Dropping impressions for key: " . $key);
                $impressionCache->removeImpression($key, $impressions);
            }

            Di::getLogger()->info("Sent Impressions removed from cache successfuly");
        } catch (\Exception $e) {
            Di::getLogger()->error($e->getMessage());
        }
    }
}
