<?php

namespace SplitIO\Component\Cache;

/**
 * Class CacheKeyTrait
 * @package SplitIO\Cache
 */
class CacheKeyTrait
{
    /**
     * @param $key
     */
    protected function assertValidKey($key)
    {
        if (! $this->isValidCacheKey($key)) {
            throw new \InvalidArgumentException("Invalid key format");
        }
    }

    /**
     * Validate PSR-6 cache key
     * @param $key
     * @return bool
     */
    protected function isValidCacheKey($key)
    {
        if (!is_string($key)) {
            return false;
        }

        if (strlen($key) > 255) {
            return false;
        }

        //Valid characters for a key.
        $re = "/[A-Za-z0-9_.-]+/";

        if (preg_match($re, $key, $matches) === 1) {
            if (strlen($matches[0]) !== strlen($key)) {
                return false;
            }
            return true;
        }

        return false;
    }
}
