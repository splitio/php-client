<?php

namespace SplitIO\Component\Cache;

class KeyFactory
{
    public static function make($template, $extraValues = array())
    {
        $values = array_merge(
            array(
                '{sdk-language-version}' => 'php-' . \SplitIO\version(),
                '{instance-id}' => \SplitIO\getHostIpAddress()
            ),
            $extraValues
        );
        return strtr($template, $values);
    }
}
