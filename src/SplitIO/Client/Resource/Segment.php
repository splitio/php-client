<?php
namespace SplitIO\Client\Resource;

use SplitIO\Client\ClientBase;
use SplitIO\Common\Di;
use SplitIO\Grammar\Condition\Matcher\SegmentData;
use SplitIO\Http\Client as HttpClient;
use SplitIO\Http\MethodEnum;

class Segment extends ClientBase
{
    const KEY_TILL_CACHED_ITEM = 'SPLITIO.segment.{segment_name}.till';

    private $till = -1;

    private $servicePath = '/api/segmentChanges/';

    public function getSegmentChanges($segmentName)
    {
        //Fetching next since (till) value from cache.
        $cacheKey = str_replace('{segment_name}', $segmentName, self::KEY_TILL_CACHED_ITEM);
        $since_cached_item = Di::getInstance()->getCache()->getItem($cacheKey);

        $servicePath = $this->servicePath . $segmentName;

        if ($since_cached_item->isHit()) {
            $servicePath .= '?since=' . $since_cached_item->get();
        }

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

            $this->till = (isset($segment['till'])) ? $segment['till'] : -1;

            //Updating next since (till) value.
            if ($this->till != $since_cached_item->get()) {
                $since_cached_item->set($this->till);
            }
            $since_cached_item->expiresAfter(Di::getInstance()->getSplitSdkConfiguration()->getCacheItemTtl());

            Di::getInstance()->getCache()->save($since_cached_item);

            return $segment;
        }

        return false;
    }

    public function addSegmentOnCache(array $segmentData)
    {
        $di = Di::getInstance();
        $cache = $di->getCache();

        $segmentName = $segmentData['name'];

        $segmentDataCacheItem = $cache->getItem(\SplitIO\getCacheKeyForSegmentData($segmentName));

        if ($segmentDataCacheItem->isHit()) { //Update Segment Data.

            $segment = json_decode($segmentDataCacheItem->get(), true);

            if ($segment) {

                $currentUsers = $segment['added'];
                $removedUsers = $segmentData['removed'];

                $allUsers = array_merge($currentUsers, $segmentData['added']);

                $segment['added'] = array_diff($allUsers, $removedUsers);
                $segment['removed'] = ($removedUsers);
                $segment['since'] = $segmentData['since'];
                $segment['till'] = $segmentData['till'];
            }

        } else { //Create Segment Data.
            $segment = $segmentData;
        }

        $segmentDataCacheItem->set(json_encode($segment));
        $segmentDataCacheItem->expiresAfter(Di::getInstance()->getSplitSdkConfiguration()->getCacheItemTtl());

        return $cache->save($segmentDataCacheItem);
    }

}