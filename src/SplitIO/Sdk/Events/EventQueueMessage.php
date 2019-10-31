<?php
namespace SplitIO\Sdk\Events;

use SplitIO\Sdk\QueueMetadataMessage;

class EventQueueMessage
{
    /**
     * @var
     */
    private $metadata;

    /**
     * @var
     */
    private $event;

    /**
     * EventQueueMessage constructor.
     * @param $metadata
     * @param $event
     */
    public function __construct(QueueMetadataMessage $metadata, EventDTO $event)
    {
        $this->metadata = $metadata;
        $this->event = $event;
    }


    /**
     * @return QueueMetadataMessage
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param mixed $metadata
     */
    public function setMetadata(QueueMetadataMessage $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @return EventDTO
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     */
    public function setEvent(EventDTO $event)
    {
        $this->event = $event;
    }

    public function toArray()
    {
        return array(
            'm' => $this->metadata->toArray(),
            'e' => $this->event->toArray()
        );
    }
}
