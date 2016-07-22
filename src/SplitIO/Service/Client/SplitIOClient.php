<?php
namespace SplitIO\Service\Client;

use SplitIO\Service\Client\Resource\MetricsResource;
use SplitIO\Service\Client\Resource\SegmentResource;
use SplitIO\Service\Client\Resource\SplitResource;
use SplitIO\Service\Client\Resource\TestImpressionResource;
use SplitIO\Component\Common\Di;
use SplitIO\Component\Utils as SplitIOUtils;

/**
 * Class SplitIOClient
 *
 * The Split Facade Client.
 *
 * @package SplitIO
 */
class SplitIOClient
{
    private $config = null;

    public function __construct($apiKey)
    {

        $serverSDKUrl = SplitIOUtils\getSplitServerUrl();
        $serverEventsUrl = SplitIOUtils\getSplitEventsUrl();

        $config = new Config();
        $config->setUrl($serverSDKUrl);
        $config->setEventsUrl($serverEventsUrl);
        $config->setAuthorization($apiKey);

        $this->config = $config;

        //Adding Client configuration as Di value for all Client Resources.
        Di::set(Di::KEY_SPLIT_CLIENT_CONFIG, $config);
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

    /**
     * @return bool
     */
    public function sendTestImpressions()
    {
        $impressionsResource = new TestImpressionResource();
        return $impressionsResource->sendTestImpressions();
    }

    public function sendMetrics()
    {
        $metricsResource = new MetricsResource();
        return $metricsResource->sendMetrics();
    }
}
