<?php
namespace SplitIO\Sdk;

/**
 * Class Key
 * @package SplitIO\Sdk
 */
class Key
{
    /**
     * @var
     */
    private $matchingKey;

    /**
     * @var
     */
    private $bucketingKey;

    /**
     * @param $matchingKey
     * @param $bucketingKey
     */
    public function __construct($matchingKey, $bucketingKey)
    {
        $this->matchingKey = $matchingKey;
        $this->bucketingKey = $bucketingKey;
    }

    /**
     * @return mixed
     */
    public function getMatchingKey()
    {
        return $this->matchingKey;
    }

    /**
     * @param mixed $matchingKey
     */
    public function setMatchingKey($matchingKey)
    {
        $this->matchingKey = $matchingKey;
    }

    /**
     * @return mixed
     */
    public function getBucketingKey()
    {
        return $this->bucketingKey;
    }

    /**
     * @param mixed $bucketingKey
     */
    public function setBucketingKey($bucketingKey)
    {
        $this->bucketingKey = $bucketingKey;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf("Matching key: %s  -- Bucketing key: %s", $this->matchingKey, $this->bucketingKey);
    }
}
