<?php
namespace SplitIO\Service\Client\Resource;

use SplitIO\Component\Cache\BlockUntilReadyCache;
use SplitIO\Component\Cache\SplitCache;
use SplitIO\Component\Common\Di;

class SplitResource extends SdkTypeResource
{
    private $servicePath = '/api/splitChanges';

    const KEY_SINCE_CACHED_ITEM = 'SPLITIO.splits.since';

    const KEY_TILL_CACHED_ITEM = 'SPLITIO.splits.till';

    /**
     * @return bool|null
     */
    public function getSplitChanges()
    {

        //Fetching next since value from cache.
        $splitCache = new SplitCache();
        $since_cached_value = $splitCache->getChangeNumber();
        Di::getLogger()->info("SINCE CACHED VALUE: $since_cached_value");
        $servicePath = $this->servicePath . '?since='.$since_cached_value;

        Di::getLogger()->info("SERVICE PATH: $servicePath");

        $response = $this->get($servicePath);

        if ($response->isSuccessful()) {
            $splitChanges = json_decode($response->getBody(), true);

            if (isset($splitChanges['since']) && isset($splitChanges['till'])
                && $splitChanges['since'] == $splitChanges['till']  ) {
                Di::getLogger()->info("Registering splits ready mark");
                $dateTimeUTC = new \DateTime("now", new \DateTimeZone("UTC"));
                $bur = new BlockUntilReadyCache();
                $bur->setReadySplits($dateTimeUTC->getTimestamp());
            }

            $splits = (isset($splitChanges['splits'])) ? $splitChanges['splits'] : false;

            if (!$splits) {
                Di::getLogger()->notice("Splits returned by the server are empty");
                return false;
            }

            return $splitChanges;
        }

        return false;
    }
}
