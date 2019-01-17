<?php

namespace SplitIO\Sdk\Validator;

use SplitIO\Split as SplitApp;
use SplitIO\Sdk\Key;
use SplitIO\Component\Utils as SplitIOUtils;

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
        if (is_null($value)) {
            SplitApp::logger()->critical($operation . ": you passed a null " . $name . ", " . $name .
                " must be a non-empty string.");
            return false;
        }
        if (!is_string($value)) {
            SplitApp::logger()->critical($operation . ": you passed an invalid " . $name . ", " . $name .
                " must be a non-empty string.");
            return false;
        }
        if (empty($value)) {
            SplitApp::logger()->critical($operation . ": you passed an empty " . $name . ", " . $name .
                " must be a non-empty string.");
            return false;
        }
        return true;
    }

    /**
     * @param $key
     * @param $operation
     * @return mixed|null
     */
    public static function validateKey($key, $operation)
    {
        if (is_null($key)) {
            SplitApp::logger()->critical($operation . ": you passed a null key, the key must be a non-empty string.");
            return null;
        }
        if ($key instanceof Key) {
            return array(
                'matchingKey' => $key->getMatchingKey(),
                'bucketingKey' => $key->getBucketingKey()
            );
        }
        $strKey = \SplitIO\toString($key, 'key', $operation);
        if ($strKey === false) {
            SplitApp::logger()->critical($operation . ': you passed an invalid key type,'
                . ' key must be a non-empty string.');
            return null;
        }
        if (empty($strKey)) {
            SplitApp::logger()->critical($operation . ": you passed an empty key, key must be a non-empty string.");
            return null;
        }
        if (strlen($strKey) > 250) {
            SplitApp::logger()->critical($operation . ": key too long - must be 250 characters or less.");
            return null;
        }
        return array(
            'matchingKey' => $strKey,
            'bucketingKey' => null
        );
    }

    /**
     * @param $featureName
     * @return string|null
     */
    public static function validateFeatureName($featureName)
    {
        return self::validString($featureName, 'split name', 'getTreatment') ? $featureName : null;
    }

    /**
     * @param $key
     * @return string|null
     */
    public static function validateTrackKey($key)
    {
        if (is_null($key)) {
            SplitApp::logger()->critical("track: you passed a null key, key must be a non-empty string.");
            return null;
        }
        $strKey = \SplitIO\toString($key, 'key', 'track');
        if ($strKey === false) {
            SplitApp::logger()->critical('track: you passed an invalid key type, key must be a non-empty string.');
            return null;
        }
        if (empty($strKey)) {
            SplitApp::logger()->critical('track: you passed an empty key, key must be a non-empty string.');
            return null;
        }
        if (strlen($strKey) > 250) {
            SplitApp::logger()->critical('track: key too long - must be 250 characters or less.');
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
        if (!preg_match('/[a-zA-Z0-9][-_.:a-zA-Z0-9]{0,79}/', $eventType)) {
            SplitApp::logger()->critical('track: eventType must adhere to the regular expression '
                . '[a-zA-Z0-9][-_.:a-zA-Z0-9]{0,79}. This means an event name must be alphanumeric, '
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
        if (is_null($featureName)) {
            SplitApp::logger()->warning('getTreatments: null featureName was filtered.');
            return false;
        }
        if (!is_string($featureName)) {
            SplitApp::logger()->warning('getTreatments: filtered featureName for not being string.');
            return false;
        }
        return true;
    }

    /**
     * @param $featureNames
     * @return array|null
     */
    public static function validateGetTreatments($featureNames)
    {
        if (is_null($featureNames) || !is_array($featureNames)) {
            SplitApp::logger()->critical('getTreatments: featureNames must be a non-empty array.');
            return null;
        }
        $filteredArray = array_values(
            array_unique(
                array_filter($featureNames, "self::validFeatureNameFromTreatments")
            )
        );
        if (count($filteredArray) == 0) {
            SplitApp::logger()->critical('getTreatments: featureNames must be a non-empty array.');
            return null;
        }
        return $filteredArray;
    }

    /**
     * @param $featureName
     * @return string|null
     */
    public static function validateManager($featureName)
    {
        return self::validString($featureName, 'split name', 'split') ? $featureName : null;
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
