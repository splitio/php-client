<?php
namespace SplitIO;

use SplitIO\Grammar\Condition\Partition\TreatmentEnum;

function version()
{
    return Version::CURRENT;
}

function ToInteger($x)
{
    $x = (int) $x;
    return $x < 0 ? ceil($x) : floor($x);
}

function modulo($a, $b)
{
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

    $result = array();

    foreach ($lines as $line) {
        $line = trim($line);
        if (isset($line[0]) && $line[0] != '#') {
            $matches = array();
            if (preg_match($re, $line, $matches)) {
                if (isset($matches[1]) && isset($matches[2])) {
                    $result[$matches[1]]["treatment"] = $matches[2];
                }
            }
        }
    }

    return $result;
}

function getHostIpAddress()
{
    $diIpAddress = \SplitIO\Component\Common\Di::getIPAddress();
    if (!is_null($diIpAddress) && is_string($diIpAddress) && trim($diIpAddress)) {
        return $diIpAddress;
    } elseif (isset($_SERVER['SERVER_ADDR']) && is_string($_SERVER['SERVER_ADDR'])
        && trim($_SERVER['SERVER_ADDR'])) {
        return $_SERVER['SERVER_ADDR'];
    } else {
        return 'unknown';
    }
}
