<?php
namespace SplitIO\Client\Resource;

use SplitIO\Cache\SplitCache;
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

    const KEY_TILL_CACHED_ITEM = 'SPLITIO.splits.till';

    /**
     * @return bool|null
     */
    public function getSplitChanges()
    {

        //Fetching next since value from cache.
        $since_cached_value = (new SplitCache())->getChangeNumber();

        $servicePath = $this->servicePath . '?since='.$since_cached_value;

        Di::getInstance()->getLogger()->info("SERVICE PATH: $servicePath");

        $request = $this->getRequest(MethodEnum::GET(), $servicePath);

        $httpClient = new HttpClient();
        $response = $httpClient->send($request);

        if ($response->isSuccess()) {

            $splitChanges = json_decode($response->getBody(), true);

            $splits = (isset($splitChanges['splits'])) ? $splitChanges['splits'] : false;

            if (!$splits) {
                Di::getInstance()->getLogger()->notice("Splits returned by the server are empty");
                return false;
            }

            return $splitChanges;
        }

        return false;
    }
}