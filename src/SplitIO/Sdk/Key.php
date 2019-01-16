<?php
namespace SplitIO\Sdk;

use SplitIO\Sdk\Validator\InputValidator;
use SplitIO\Exception\KeyException;

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
        $strMatchingKey = \SplitIO\toString($matchingKey, "matchingKey", "getTreatment");
        if ((!$strMatchingKey) || (empty($strMatchingKey))) {
            throw new KeyException("getTreatment: you passed " . \SplitIO\converToString($matchingKey) .
                ", matchingKey must be a non-empty string.");
        }
        $this->matchingKey = $strMatchingKey;
        $strBucketingKey = \SplitIO\toString($bucketingKey, "bucketingKey", "getTreatment");
        if ((!$strBucketingKey) || (empty($strBucketingKey))) {
            throw new KeyException("getTreatment: you passed " . \SplitIO\converToString($bucketingKey) .
                ", bucketingKey must be a non-empty string.");
        }
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
