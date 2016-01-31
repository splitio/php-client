<?php
namespace SplitIO;

function version()
{
    return Sdk::VERSION;
}

//CACHE Functions

function getCacheKeyForSplit($splitName)
{
    return str_replace('{splitName}', $splitName, 'SPLITIO.split.{splitName}');
}

function getCacheKeyForSegmentData($segmentName)
{
    return str_replace('{segmentName}', $segmentName, 'SPLITIO.segmentData.{segmentName}');
}

function getCacheKeyForRegisterSegments()
{
    return 'SPLITIO.segments.registered';
}

//HASH Functions
function hash($key, $seed){
    //return splitHash($key, $seed);
    return murmurhash3_int($key, $seed);
}


function splitHash($key, $seed) {

    //return microtime() * rand(1,$seed);

    $h = (integer) $seed;

    for ($i = 0; $i < strlen($key); $i++) {

        //$h = 31 * abs((integer)$h) + (integer) $key[$i];
        $h = 31 * (integer) $h + ord($key[$i]);

        //echo $h.PHP_EOL;
    }

    return  (integer) $h;
}


/**
 * PHP Implementation of MurmurHash3
 *
 * function murmurhash3($key,$seed=0){
 *   return base_convert(murmurhash3_int($key,$seed),10,32);
 * }
 *
 *
 *
 * @author Stefano Azzolini (lastguest@gmail.com)
 * @see https://github.com/lastguest/murmurhash-php
 * @author Gary Court (gary.court@gmail.com)
 * @see http://github.com/garycourt/murmurhash-js
 * @author Austin Appleby (aappleby@gmail.com)
 * @see http://sites.google.com/site/murmurhash/
 *
 * @param  string $key   Text to hash.
 * @param  number $seed  Positive integer only
 * @return number 32-bit (base 32 converted) positive integer hash
 */

function murmurhash3_int($key,$seed=0){
    $key = (string) $key;
    $klen = strlen($key);
    $h1   = $seed;
    for ($i = 0, $bytes = $klen - ($remainder = $klen & 3); $i < $bytes;) {
        $k1 = ((ord($key[$i]) & 0xff))
            | ((ord($key[++$i]) & 0xff) << 8)
            | ((ord($key[++$i]) & 0xff) << 16)
            | ((ord($key[++$i]) & 0xff) << 24);
        ++$i;
        $k1 = (((($k1 & 0xffff) * 0xcc9e2d51) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0xcc9e2d51) & 0xffff) << 16))) & 0xffffffff;
        $k1 = $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7fffffff) >> 17) | 0x4000);
        $k1 = (((($k1 & 0xffff) * 0x1b873593) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0x1b873593) & 0xffff) << 16))) & 0xffffffff;
        $h1 ^= $k1;
        $h1 = $h1 << 13 | ($h1 >= 0 ? $h1 >> 19 : (($h1 & 0x7fffffff) >> 19) | 0x1000);
        $h1b = (((($h1 & 0xffff) * 5) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 5) & 0xffff) << 16))) & 0xffffffff;
        $h1 = ((($h1b & 0xffff) + 0x6b64) + ((((($h1b >= 0 ? $h1b >> 16 : (($h1b & 0x7fffffff) >> 16) | 0x8000)) + 0xe654) & 0xffff) << 16));
    }
    $k1 = 0;
    switch ($remainder) {
        case 3:
            $k1 ^= (ord($key[$i + 2]) & 0xff) << 16;
        case 2:
            $k1 ^= (ord($key[$i + 1]) & 0xff) << 8;
        case 1:
            $k1 ^= (ord($key[$i]) & 0xff);
            $k1 = ((($k1 & 0xffff) * 0xcc9e2d51) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0xcc9e2d51) & 0xffff) << 16)) & 0xffffffff;
            $k1 = $k1 << 15 | ($k1 >= 0 ? $k1 >> 17 : (($k1 & 0x7fffffff) >> 17) | 0x4000);
            $k1 = ((($k1 & 0xffff) * 0x1b873593) + ((((($k1 >= 0 ? $k1 >> 16 : (($k1 & 0x7fffffff) >> 16) | 0x8000)) * 0x1b873593) & 0xffff) << 16)) & 0xffffffff;
            $h1 ^= $k1;
    }
    $h1 ^= $klen;
    $h1 ^= ($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000);
    $h1 = ((($h1 & 0xffff) * 0x85ebca6b) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 0x85ebca6b) & 0xffff) << 16)) & 0xffffffff;
    $h1 ^= ($h1 >= 0 ? $h1 >> 13 : (($h1 & 0x7fffffff) >> 13) | 0x40000);
    $h1 = (((($h1 & 0xffff) * 0xc2b2ae35) + ((((($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000)) * 0xc2b2ae35) & 0xffff) << 16))) & 0xffffffff;
    $h1 ^= ($h1 >= 0 ? $h1 >> 16 : (($h1 & 0x7fffffff) >> 16) | 0x8000);
    return $h1;
}


function uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
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