<?php
namespace SplitIO;

use SplitIO\Client\Config;
use SplitIO\Client\Resource\Segment;
use SplitIO\Client\Resource\Split;
use SplitIO\Common\Di;
use SplitIO\Grammar\Condition\Matcher\SegmentData;

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
        $splitChanges = new Split();

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
        $segmentChanges = new Segment();

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
        $segmentChanges = new Segment();
        $rawSegmentData = $segmentChanges->getSegmentChanges($segmentName);

        if ($rawSegmentData) {
            if ($segmentChanges->addSegmentOnCache($rawSegmentData)) {
                return $rawSegmentData;
            }
        }

        return false;
    }
}
