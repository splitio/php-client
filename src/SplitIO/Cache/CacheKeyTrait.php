<?php
namespace SplitIO\Cache;

use \SplitIO\Cache\Exception\InvalidArgumentException;

/**
 * Class CacheKeyTrait
 * @package SplitIO\Cache
 */
trait CacheKeyTrait
{
    private function assertValidKey($key)
    {
        if (! \SplitIO\isValidCacheKey($key)) {
            throw new InvalidArgumentException("Invalid key format");
        }
    }
}