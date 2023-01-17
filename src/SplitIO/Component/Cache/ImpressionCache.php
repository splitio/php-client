<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Context;
use SplitIO\Component\Cache\KeyFactory;
use SplitIO\Sdk\QueueMetadataMessage;
use SplitIO\Component\Cache\Pool;

class ImpressionCache
{
    const IMPRESSIONS_QUEUE_KEY = "SPLITIO.impressions";
    const IMPRESSION_KEY_DEFAULT_TTL = 3600;

    /**
     * @var \SplitIO\Component\Cache\Pool
     */
    private $cache;

    /**
     * @param \SplitIO\Component\Cache\Pool $cache
     */
    public function __construct(Pool $cache)
    {
        $this->cache = $cache;
    }

    public function logImpressions($impressions, QueueMetadataMessage $metadata)
    {
        $toStore = array_map(
            function ($imp) use ($metadata) {
                return json_encode(array(
                    'm' => $metadata->toArray(),
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

        Context::getLogger()->debug("Adding impressions into queue: ". implode(",", $toStore));
        $count = $this->cache->rightPushInList(self::IMPRESSIONS_QUEUE_KEY, $toStore);
        if ($count == count($impressions)) {
            $this->cache->expireKey(self::IMPRESSIONS_QUEUE_KEY, self::IMPRESSION_KEY_DEFAULT_TTL);
        }
        return ($count >= count($impressions));
    }
}
