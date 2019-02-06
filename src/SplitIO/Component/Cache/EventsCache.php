<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;
use SplitIO\Sdk\Events\EventQueueMessage;

class EventsCache
{
    const KEY_EVENTS_LIST = "SPLITIO.events";

    public static function addEvent(EventQueueMessage $message)
    {
        $queueJSONmessage =  json_encode($message->toArray());

        Di::getLogger()->debug("Adding event item into queue: ". $queueJSONmessage);
        return (Di::getCache()->rightPushInList(self::KEY_EVENTS_LIST, $queueJSONmessage) > 0);
    }
}
