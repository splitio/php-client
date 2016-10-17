<?php
namespace SplitIO\Service\Client\Resource;

use SplitIO\Service\Client\ClientBase;
use SplitIO\Component\Common\Di;
use SplitIO\Component\Http\ResponseHelper;
use SplitIO\Component\Cache\SegmentCache;

class SegmentResource extends SdkTypeResource
{
    private $till = -1;

    private $servicePath = '/api/segmentChanges/';

    public function getSegmentChanges($segmentName)
    {
        //Fetching next since (till) value from cache.
        $segmentCache = new SegmentCache();
        $since_cached_value = $segmentCache->getChangeNumber($segmentName);

        $servicePath = $this->servicePath . $segmentName . '?since=' . $since_cached_value;

        Di::getLogger()->info("SERVICE PATH: $servicePath");

        try {
            //GETting data from server
            $response = $this->get($servicePath);

            if ($response->isSuccessful()) {
                $segment = json_decode($response->getBody(), true);

                //Returning false due the server has not changes
                if (isset($segment['since']) && isset($segment['till']) && $segment['since'] >= $segment['till']) {
                    Di::getLogger()->notice("Segments returned by the server are empty");
                    return false;
                }

                return $segment;
            }
        } catch (\Exception $e) {
            Di::getLogger()->error($e->getMessage());
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
