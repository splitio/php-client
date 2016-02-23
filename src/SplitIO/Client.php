<?php
namespace SplitIO;

use SplitIO\Cache\ImpressionCache;
use SplitIO\Client\Config;
use SplitIO\Client\Resource\Segment as SegmentResource;
use SplitIO\Client\Resource\Split as SplitResource;
use SplitIO\Client\Resource\TestImpression as TestImpressionResource;
use SplitIO\Common\Di;

/**
 * Class Client
 *
 * The Split Facade Client.
 *
 * @package SplitIO
 */
class Client
{
    private $config = null;

    public function __construct($url, $auth)
    {
        $config = new Config();
        $config->setUrl($url);
        $config->setAuthorization($auth);

        $this->config = $config;

        //Adding Client configuration as Di value for all Client modules.
        Di::getInstance()->setSplitClientConfiguration($config);
    }

    /**
     * @return bool|null
     */
    public function getSplitChanges()
    {
        $splitChanges = new SplitResource();

        $data = $splitChanges->getSplitChanges();

        if ($data !== false) {
            return $data;
        }

        return false;
    }

    /**
     * @param $segmentName
     * @return bool|mixed
     */
    public function getSegmentChanges($segmentName)
    {
        $segmentChanges = new SegmentResource();

        $data = $segmentChanges->getSegmentChanges($segmentName);

        if ($data !== false) {
            return $data;
        }

        return false;
    }

    /**
     * @param $segmentName
     * @return bool|array
     */
    public function updateSegmentChanges($segmentName)
    {
        $segmentChanges = new SegmentResource();
        $rawSegmentData = $segmentChanges->getSegmentChanges($segmentName);

        if ($rawSegmentData) {
            if ($segmentChanges->addSegmentOnCache($rawSegmentData)) {
                return $rawSegmentData;
            }
        }

        return false;
    }

    public function sendTestImpressions()
    {
        $impressionKeys = Split::cache()->getKeys(ImpressionCache::getCacheKeySearchPattern());
        $impressionsResource = new TestImpressionResource();
        $impressionCache = new ImpressionCache();

        $result = [];

        foreach ($impressionKeys as $key) {

            $featureName = ImpressionCache::getFeatureNameFromKey($key);
            $cachedImpressions = $impressionCache->getAllImpressions($key);
            $impressions = [];

            //restoring cached impressions from JSON string to PHP Array.
            for ($i=0; $i < count($cachedImpressions); $i++) {
                $impressions[$i] = json_decode($cachedImpressions[$i], true);
            }

            //Sending Impressions dataset.
            $resp = $impressionsResource->sendTestImpressions($featureName, $impressions);

            //removing sent impressions from cache.
            if ($resp === true) {
                for ($i=0; $i < count($cachedImpressions); $i++) {
                    $impressionCache->removeImpression($key, $cachedImpressions[$i]);
                }
            }
            //Status control for each sent impression.
            $result[$featureName] = ['cacheKey' => $key, 'response' => $resp];
        }

        return $result;
    }
}
