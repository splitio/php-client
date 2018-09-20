<?php
namespace SplitIO\Sdk\Events;

use SplitIO\Exception\Exception;

class EventDTO
{
    private $key;
    private $trafficTypeName;
    private $eventTypeId;
    private $value;
    private $timestamp;

    public function __construct($key, $trafficTypeName, $eventTypeId, $value)
    {
        if (empty($key)) {
            throw new Exception("Key must not be empty");
        }

        if (empty($trafficTypeName)) {
            throw new Exception("track: trafficType must not be an empty String");
        }

        if (empty($eventTypeId)) {
            throw new Exception("EventTypeId must not be empty");
        }

        $this->key = $key;
        $this->trafficTypeName = $trafficTypeName;
        $this->eventTypeId = $eventTypeId;
        $this->value = $value;

        $this->timestamp = round(microtime(true) * 1000);
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getTrafficTypeName()
    {
        return $this->trafficTypeName;
    }

    /**
     * @param mixed $trafficTypeName
     */
    public function setTrafficTypeName($trafficTypeName)
    {
        $this->trafficTypeName = $trafficTypeName;
    }

    /**
     * @return mixed
     */
    public function getEventTypeId()
    {
        return $this->eventTypeId;
    }

    /**
     * @param mixed $eventTypeId
     */
    public function setEventTypeId($eventTypeId)
    {
        $this->eventTypeId = $eventTypeId;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }


    public function toArray()
    {
        return array(
            'key' => $this->key,
            'trafficTypeName' => $this->trafficTypeName,
            'eventTypeId' => $this->eventTypeId,
            'value' => $this->value,
            'timestamp'=> $this->timestamp
        );
    }
}
