<?php

namespace SplitIO\Component\Cache;

class KeyFactory
{

    const VALUE_TEMPLATE = "{sdk}/{instanceId}/impressions.{feature}";

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

    public static function makeRegisteredValue($featureName)
    {
        return strtr(
            self::VALUE_TEMPLATE,
            array(
                '{sdk}' => 'php-' . \SplitIO\version(),
                '{instanceId}' => \SplitIO\getHostIpAddress(),
                '{feature}' => $featureName,
            )
        );
    }

    public static function getImpressionSetKey()
    {
        return 'SPLITIO.impressionKeys';
    }
}
