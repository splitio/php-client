<?php
namespace SplitIO;

use SplitIO\Client\Config;
use SplitIO\Client\Resource\Segment;
use SplitIO\Client\Resource\Split;
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

    public function getSplitChanges()
    {
        $splitChanges = new Split();

        $data = $splitChanges->getSplitChanges();

        if ($data !== false) {
            return $data;
        }

        return false;
    }


    public function getSegmentChanges($segmentName)
    {

        $segmentChanges = new Segment();

        $data = $segmentChanges->getSegmentChanges($segmentName);

        if ($data !== false) {
            return $data;
        }

        return false;
    }


}