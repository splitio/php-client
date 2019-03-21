<?php

namespace SplitIO\Sdk\Validator;

use SplitIO\Split as SplitApp;
use SplitIO\Sdk\Key;
use SplitIO\Component\Utils as SplitIOUtils;
use SplitIO\Component\Cache\SplitCache;

const MAX_LENGTH = 250;
const REG_EXP_EVENT_TYPE = "/^[a-zA-Z0-9][-_.:a-zA-Z0-9]{0,79}$/";

class InputValidator
{
    /**
     * @param $value
     * @param $name
     * @param $operation
     * @return true|false
     */
    private static function validString($value, $name, $operation)
    {
        if (self::checkIsNull($value, $name, $operation) or self::checkIsNotString($value, $name, $operation)
        or self::checkIsEmpty($value, $name, $operation)) {
            return false;
        }
        return true;
    }

    /**
     * Try to convert primitive types to string, otherwise returns FALSE
     * Example:
     *     $stringVal = toString(34)
     *     if ($stringVal !== false) {
     *        //Do some stuff with your string val
     *     }
     *
     * @param $var
     * @return bool|string
     * @deprecated primitive data conversion will be removed in future version.
     */
    public static function toString($var, $name, $operation)
    {
        if (is_string($var)) {
            return $var;
        }
        if (is_int($var) || (is_float($var) && is_finite($var))) {
            SplitApp::logger()->warning($operation . ": " . $name . " '" . json_encode($var)
                . "' is not of type string, converting.");
            return "$var";
        }
        return false;
    }

    private static function checkIsNull($value, $name, $operation)
    {
        if (is_null($value)) {
            SplitApp::logger()->critical($operation . ": you passed a null " . $name . ", " . $name .
                " must be a non-empty string.");
            return true;
        }
        return false;
    }

    private static function checkIsEmpty($value, $name, $operation)
    {
        $trimmed = trim($value);
        if (empty($trimmed)) {
            SplitApp::logger()->critical($operation . ": you passed an empty " . $name . ", " . $name .
                " must be a non-empty string.");
            return true;
        }
        return false;
    }

    private static function checkIsNotString($value, $name, $operation)
    {
        if (!is_string($value)) {
            SplitApp::logger()->critical($operation . ": you passed an invalid " . $name . ", " . $name .
                " must be a non-empty string.");
            return true;
        }
        return false;
    }

    private static function checkNotProperLength($value, $name, $operation)
    {
        if (strlen($value) > MAX_LENGTH) {
            SplitApp::logger()->critical($operation . ": " . $name . " too long - must be " . MAX_LENGTH .
                " characters or less.");
            return true;
        }
        return false;
    }

    /**
     * @param $key
     * @param $operation
     * @return mixed|null
     */
    public static function validateKey($key, $operation)
    {
        if (self::checkIsNull($key, "key", $operation)) {
            return null;
        }
        if ($key instanceof Key) {
            return array(
                'matchingKey' => $key->getMatchingKey(),
                'bucketingKey' => $key->getBucketingKey()
            );
        }
        $strKey = self::toString($key, 'key', $operation);
        if ($strKey === false) {
            SplitApp::logger()->critical($operation . ': you passed an invalid key type,'
                . ' key must be a non-empty string.');
            return null;
        }
        if (self::checkIsEmpty($strKey, "key", $operation) or self::checkNotProperLength($strKey, "key", $operation)) {
            return null;
        }
        
        return array(
            'matchingKey' => $strKey,
            'bucketingKey' => null
        );
    }

    private static function trimFeatureName($featureName, $operation = "getTreatments")
    {
        $trimmed = trim($featureName);
        if ($trimmed !== $featureName) {
            SplitApp::logger()->warning($operation . ": split name " . json_encode($featureName) . " has extra " .
            "whitespace, trimming.");
        }
        return $trimmed;
    }

    public static function isSplitInCache($featureName, $operation)
    {
        try {
            $splitCache = new SplitCache();
            if (empty($splitCache->getSplit($featureName))) {
                SplitApp::logger()->critical($operation . ": you passed '" . $featureName .
                "' that does not exist in this environment, please double check what Splits exist in the web console.");
                return false;
            }
            return true;
        } catch (\Exception $e) {
            SplitApp::logger()->critical($operation . ' method is throwing exceptions');
            SplitApp::logger()->critical($e->getMessage());
            SplitApp::logger()->critical($e->getTraceAsString());
            return false;
        }
    }

    /**
     * @param $featureName
     * @param $operation
     * @return string|null
     */
    public static function validateFeatureName($featureName, $operation)
    {
        return self::validString($featureName, 'split name', $operation) &&
            self::isSplitInCache(self::trimFeatureName($featureName, $operation), $operation) ?
            self::trimFeatureName($featureName, $operation) : null;
    }

    /**
     * @param $key
     * @return string|null
     */
    public static function validateTrackKey($key)
    {
        if (self::checkIsNull($key, "key", "track")) {
            return null;
        }
        $strKey = self::toString($key, 'key', 'track');
        if ($strKey === false) {
            SplitApp::logger()->critical('track: you passed an invalid key type, key must be a non-empty string.');
            return null;
        }
        if (self::checkIsEmpty($strKey, "key", "track") or self::checkNotProperLength($strKey, "key", "track")) {
            return null;
        }
        return $strKey;
    }

    /**
     * @param $trafficType
     * @return string|null
     */
    public static function validateTrafficType($trafficType)
    {
        if (!self::validString($trafficType, 'traffic type', 'track')) {
            return null;
        }
        $toLowercase = strtolower($trafficType);
        if ($toLowercase !== $trafficType) {
            SplitApp::logger()->warning("track: '" . $trafficType . "' should be all lowercase - converting string to "
                . "lowercase.");
        }
        return $toLowercase;
    }

    /**
     * @param $eventType
     * @return string|null
     */
    public static function validateEventType($eventType)
    {
        if (!self::validString($eventType, 'event type', 'track')) {
            return null;
        }
        if (!preg_match(REG_EXP_EVENT_TYPE, $eventType)) {
            SplitApp::logger()->critical('track: eventType must adhere to the regular expression '
                . REG_EXP_EVENT_TYPE . '. This means an event name must be alphanumeric, '
                . 'cannot be more than 80 characters long, and can only include a dash, underscore, '
                . 'period, or colon as separators of alphanumeric characters.');
            return null;
        }
        return $eventType;
    }

    /**
     * @param $value
     * @return number|null
     */
    public static function validateValue($value)
    {
        if (is_null($value)) {
            return null;
        }
        if (!(is_int($value) || is_float($value))) {
            SplitApp::logger()->critical('track: value must be a number.');
            return false;
        }
        return $value;
    }

    /**
     * @param $featureName
     * @return true|false
     */
    private static function validFeatureNameFromTreatments($featureName)
    {
        return self::validString($featureName, 'split name', 'getTreatments');
    }

    /**
     * @param $featureNames
     * @return array|null
     */
    public static function validateFeatureNames($featureNames)
    {
        if (is_null($featureNames) || !is_array($featureNames)) {
            SplitApp::logger()->critical('getTreatments: featureNames must be a non-empty array.');
            return null;
        }
        $filteredArray = array_values(
            array_map(
                "self::trimFeatureName",
                array_unique(
                    array_filter(
                        $featureNames,
                        "self::validFeatureNameFromTreatments"
                    )
                )
            )
        );
        if (count($filteredArray) == 0) {
            SplitApp::logger()->critical('getTreatments: featureNames must be a non-empty array.');
            return null;
        }
        return $filteredArray;
    }

    /**
     * @param $attributes
     * @return true|false
     */
    public static function validAttributes($attributes, $operation)
    {
        if (is_null($attributes)) {
            return true;
        }
        if (!SplitIOUtils\isAssociativeArray($attributes)) {
            SplitApp::logger()->critical($operation . ': attributes must be of type dictionary.');
            return false;
        }
        return true;
    }
}
