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
        $strMatchingKey = InputValidator::toString($matchingKey, "matchingKey", "Key");
        if ($strMatchingKey === false) {
            throw new KeyException('Key: you passed an invalid matchingKey type, matchingKey '
                . 'must be a non-empty string.');
        }
        if (empty($strMatchingKey)) {
            throw new KeyException('Key: you passed an empty string, matchingKey must be a non-empty string.');
        }
        $this->matchingKey = $strMatchingKey;
        $strBucketingKey = InputValidator::toString($bucketingKey, "bucketingKey", "Key");
        if ($strBucketingKey === false) {
            throw new KeyException('Key: you passed an invalid bucketingKey type, bucketingKey '
                . 'must be a non-empty string.');
        }
        if (empty($strBucketingKey)) {
            throw new KeyException('Key: you passed an empty string, bucketingKey must be a non-empty '
                . 'string.');
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
