<?php
namespace SplitIO;

function version()
{
    return Sdk::VERSION;
}

//HASH Functions
function hash($key, $seed)
{
    return splitHash($key, $seed);
    //return murmurhash3_int($key, $seed);
}

function ToInteger($x)
{
    $x = (int) $x;
    return $x < 0 ? ceil($x) : floor($x);
}

function modulo($a, $b) {
    return $a - floor($a/$b)*$b;
}

function ToUint32($x)
{
    return modulo(ToInteger($x), pow(2, 32));
}

function toInt32($x)
{
    $uint32 = ToUint32($x);

    if ($uint32 >= pow(2, 31)) {
        return $uint32 - pow(2, 32);
    } else {
        return $uint32;
    }
}

function splitHash($key, $seed)
{
    $h = 0;

    for ($i = 0; $i < strlen($key); $i++) {

        $h = toInt32(toInt32(31 * $h) + ord($key[$i]));

    }

    return toInt32($h ^ $seed);
}

// @codeCoverageIgnoreStart
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

function murmurhash3_int($key, $seed=0)
{
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
// @codeCoverageIgnoreEnd

/**
 * Parse the .splits file, returning an array of feature=>treatment pairs
 * Sample:
 *          #This is a comment
 *          feature_A treatment_1
 *          feature_B treatment_2
 *          feature_C treatment_1
 *
 * @param $fileContent
 * @return array
 */
function parseSplitsFile($fileContent)
{
    $re = "/([a-zA-Z]+[-_a-zA-Z0-9]*)\\s+([a-zA-Z]+[-_a-zA-Z0-9]*)/";

    $lines = explode(PHP_EOL, $fileContent);

    $result = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (isset($line[0]) && $line[0] != '#') {
            $matches = [];
            if (preg_match($re, $line, $matches)) {
                if (isset($matches[1]) && isset($matches[2])) {
                    $result[$matches[1]] = $matches[2];
                }
            }
        }
    }

    return $result;
}