<?php
/**
 * Helper functions
 */
namespace SplitIO\Component\Utils;

function isAssociativeArray($arr)
{
    if (!is_array($arr)) {
        return false;
    }
    return array_keys($arr) !== range(0, count($arr) - 1);
}
