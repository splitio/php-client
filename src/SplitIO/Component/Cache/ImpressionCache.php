<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;
use SplitIO\Component\Cache\KeyFactory;

class ImpressionCache
{
    const IMPRESSIONS_QUEUE_KEY = "SPLITIO.impressions";
    const IMPRESSION_KEY_DEFAULT_TTL = 3600;

    public function logImpressions($impressions, $metadata)
    {
        $toStore = array_map(
            function ($imp) use ($metadata) {
                return json_encode(array(
                    "m" => array(
                        "s" => $metadata['sdkVersion'],
                        "i" => $metadata['machineIp'],
                        "n" => $metadata['machineName'],
                    ),
                    "i" => array(
                        "k" => $imp->getId(),
                        "b" => $imp->getBucketingKey(),
                        "f" => $imp->getFeature(),
                        "t" => $imp->getTreatment(),
                        "r" => $imp->getLabel(),
                        "c" => $imp->getChangeNumber(),
                        "m" => $imp->getTime(),
                    ),
                ));
            },
            $impressions
        );

        Di::getLogger()->debug("Adding impressions into queue: ". implode(",", $toStore));
        $count = Di::getCache()->rightPushInList(self::IMPRESSIONS_QUEUE_KEY, $toStore);
        if ($count == count($impressions)) {
            Di::getCache()->expireKey(self::IMPRESSIONS_QUEUE_KEY, self::IMPRESSION_KEY_DEFAULT_TTL);
        }
        return ($count >= count($impressions));
    }
}
