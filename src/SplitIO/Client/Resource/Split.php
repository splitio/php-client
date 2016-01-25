<?php
namespace SplitIO\Client\Resource;

use SplitIO\Client\ClientBase;
use SplitIO\Common\Di;
use SplitIO\Http\Client as HttpClient;
use SplitIO\Http\MethodEnum;

class Split extends ClientBase
{
    private $since = -1;

    private $still = null;

    private $servicePath = '/api/splitChanges';

    const KEY_SINCE_CACHED_ITEM = 'SPLITIO.splits.since';
    /**
     * @return bool|null
     */
    public function getSplitChanges()
    {
        //Fetching since value from cache.
        $since_cached_item = Di::getInstance()->getCache()->getItem(self::KEY_SINCE_CACHED_ITEM);

        $servicePath = $this->servicePath;

        if ($since_cached_item->isHit()) {
            $servicePath .= '?since='.$since_cached_item->get();
        }

        Di::getInstance()->getLogger()->info("SERVICE PATH: $servicePath");

        $request = $this->getRequest(MethodEnum::GET(), $servicePath);

        $httpClient = new HttpClient();
        $response = $httpClient->send($request);

        if ($response->isSuccess()) {

            $splitChanges = json_decode($response->getBody(), true);

            $this->since = (isset($splitChanges['since'])) ? $splitChanges['since'] : -1;

            //Updating since value.
            $since_cached_item->set($this->since);
            //@TODO set expiration time from config.
            $since_cached_item->expiresAfter(300);
            Di::getInstance()->getCache()->save($since_cached_item);

            $this->till = (isset($splitChanges['till'])) ? $splitChanges['till'] : null;

            $splits = (isset($splitChanges['splits'])) ? $splitChanges['splits'] : false;

            if (!$splits) {
                Di::getInstance()->getLogger()->error("Splits returned by the server are empty");
                return false;
            }

            return $splitChanges;
        }

        return false;
    }
}