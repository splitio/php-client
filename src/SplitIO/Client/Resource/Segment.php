<?php
namespace SplitIO\Client\Resource;

use SplitIO\Client\ClientBase;
use SplitIO\Common\Di;
use SplitIO\Http\Client as HttpClient;
use SplitIO\Http\MethodEnum;
use SplitIO\Cache\SegmentCache;

class Segment extends ClientBase
{
    const KEY_TILL_CACHED_ITEM = 'SPLITIO.segment.{segment_name}.till';

    private $till = -1;

    private $servicePath = '/api/segmentChanges/';

    public function getSegmentChanges($segmentName)
    {
        //Fetching next since (till) value from cache.
        $since_cached_value = (new SegmentCache())->getChangeNumber($segmentName);

        $servicePath = $this->servicePath . $segmentName . '?since=' . $since_cached_value;

        Di::getInstance()->getLogger()->info("SERVICE PATH: $servicePath");

        $request = $this->getRequest(MethodEnum::GET(), $servicePath);

        $httpClient = new HttpClient();
        $response = $httpClient->send($request);

        if ($response->isSuccess()) {
            $segment = json_decode($response->getBody(), true);

            //Returning false due the server has not changes
            if (isset($segment['since']) && isset($segment['till']) && $segment['since'] == $segment['till']) {
                Di::getInstance()->getLogger()->notice("Segments returned by the server are empty");
                return false;
            }

            return $segment;
        }

        return false;
    }

    public function addSegmentOnCache(array $segmentData)
    {

        $segmentName = $segmentData['name'];

        $segmentCache = new SegmentCache();

        if ($segmentCache->getChangeNumber($segmentName) != $segmentData['till']) {

            $segmentCache->addToSegment($segmentName, $segmentData['added']);

            $segmentCache->removeFromSegment($segmentName, $segmentData['removed']);

            $segmentCache->setChangeNumber($segmentName, $segmentData['till']);
        }

        return true;
    }

}