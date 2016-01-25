<?php
namespace SplitIO\Client\Resource;

use SplitIO\Client\ClientBase;
use SplitIO\Common\Di;
use SplitIO\Http\Client as HttpClient;
use SplitIO\Http\MethodEnum;

class Segment extends ClientBase
{
    const KEY_SINCE_CACHED_ITEM = 'SPLITIO.segment.{segment_name}.since';

    private $since = -1;

    private $still = null;

    private $servicePath = '/api/segmentChanges/';

    public function getSegmentChanges($segmentName)
    {
        //Fetching since value from cache.
        $cacheKey = str_replace('{segment_name}', $segmentName, self::KEY_SINCE_CACHED_ITEM);
        $since_cached_item = Di::getInstance()->getCache()->getItem($cacheKey);

        $servicePath = $this->servicePath.$segmentName;

        if ($since_cached_item->isHit()) {
            $servicePath .= '?since='.$since_cached_item->get();
        }

        Di::getInstance()->getLogger()->info("SERVICE PATH: $servicePath");

        $request = $this->getRequest(MethodEnum::GET(), $servicePath);

        $httpClient = new HttpClient();
        $response = $httpClient->send($request);

        if ($response->isSuccess()) {
            $segment = json_decode($response->getBody(), true);

            $this->since = (isset($segment['since'])) ? $segment['since'] : -1;

            //Updating since value.
            $since_cached_item->set($this->since);
            //@TODO set expiration time from config.
            $since_cached_item->expiresAfter(300);
            Di::getInstance()->getCache()->save($since_cached_item);

            $this->till = (isset($segment['till'])) ? $segment['till'] : null;

            return $segment;
        }

        return false;
    }

}