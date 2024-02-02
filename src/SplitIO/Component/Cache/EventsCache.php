<?php

namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Context;
use SplitIO\Sdk\Events\EventQueueMessage;
use SplitIO\Component\Cache\Pool;

class EventsCache
{
    private const KEY_EVENTS_LIST = "SPLITIO.events";

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

    public function addEvent(EventQueueMessage $message)
    {
        $queueJSONmessage =  json_encode($message->toArray());

        Context::getLogger()->debug("Adding event item into queue: " . $queueJSONmessage);
        return ($this->cache->rightPushInList(self::KEY_EVENTS_LIST, $queueJSONmessage) > 0);
    }
}
