<?php
namespace SplitIO;

function murmur3()
{
    return "murmur-Hash";
}

/**
 * Validate PSR-6 cache key
 * @param $key
 * @return bool
 */
function isValidCacheKey($key)
{
    //$key = iconv("UTF-8", "ISO-8859-1", $key);
    if (mb_detect_encoding($key, 'UTF-8', true) !== 'UTF-8') {
        return false;
    }

    if (strlen($key) > 255) {
        return false;
    }

    //Valid characters for a key.
    $re = "/[A-Za-z0-9_.]+/";

    if (preg_match($re, $key, $matches) === 1) {
        if (strlen($matches[0]) !== strlen($key)) {
            return false;
        }
        return true;
    }

    return false;
}

/**
 * Checks if the provided value is serializable
 * @param $value
 * @return bool
 */
function isSerializable($value)
{
    if (is_resource($value)) {
        return false;
    }

    $return = true;
    $arr = array($value);

    array_walk_recursive($arr, function ($element) use (&$return) {
        if (is_object($element) && get_class($element) == 'Closure') {
            $return = false;
        }
    });

    return $return;
}