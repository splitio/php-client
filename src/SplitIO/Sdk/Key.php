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
     * Key constructor.
     * @param string $matchingKey
     * @param string $bucketingKey
     * @throws KeyException
     */
    public function __construct($matchingKey, $bucketingKey)
    {
        $strMatchingKey = \SplitIO\toString($matchingKey);
        if($strMatchingKey !== false) {
            $this->matchingKey = $matchingKey;
        } else {
            throw new KeyException("Invalid matchingKey type. Must be string");
        }

        $strBucketingKey = \SplitIO\toString($bucketingKey);
        if($strBucketingKey !== false) {
            $this->bucketingKey = $bucketingKey;
        } else {
            throw new KeyException("Invalid bucketingKey type. Must be string");
        }
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
