<?php
namespace SplitIO\Grammar\Condition\Matcher;


class SegmentData
{
    protected $name = null;

    protected $addedUsers = null;

    protected $removedUsers = null;

    protected $since = -1;

    protected $till = null;

    public function __construct($segmentData)
    {
        if ($segmentData) {

            $this->name = (isset($segmentData['name'])) ? $segmentData['name'] : '';
            $this->addedUsers = (isset($segmentData['added'])) ? $segmentData['added'] : [];
            $this->removedUsers = (isset($segmentData['removed'])) ? $segmentData['removed'] : [];
            $this->since = (isset($segmentData['since'])) ? $segmentData['since'] : -1;
            $this->till = (isset($segmentData['till'])) ? $segmentData['till'] : -1;
        }
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return null
     */
    public function getAddedUsers()
    {
        return $this->addedUsers;
    }

    /**
     * @param null $addedUsers
     */
    public function setAddedUsers($addedUsers)
    {
        $this->addedUsers = $addedUsers;
    }

    /**
     * @return null
     */
    public function getRemovedUsers()
    {
        return $this->removedUsers;
    }

    /**
     * @param null $removedUsers
     */
    public function setRemovedUsers($removedUsers)
    {
        $this->removedUsers = $removedUsers;
    }

    /**
     * @return int
     */
    public function getSince()
    {
        return $this->since;
    }

    /**
     * @param int $since
     */
    public function setSince($since)
    {
        $this->since = $since;
    }

    /**
     * @return null
     */
    public function getTill()
    {
        return $this->till;
    }

    /**
     * @param null $till
     */
    public function setTill($till)
    {
        $this->till = $till;
    }
}