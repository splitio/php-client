<?php

namespace SplitIO\Sdk\Validator;

use SplitIO\Split as SplitApp;
use SplitIO\Sdk\Key;

class InputValidator
{
    /**
     * @param $key
     * @return mixed|null
     */
    public static function validateKey($key, $operation)
    {
        if (is_null($key)) {
            SplitApp::logger()->critical($operation . ": you passed 'null', key must be a non-empty string.");
            return null;
        }
        if ($key instanceof Key) {
            return array(
                'matchingKey' => $key->getMatchingKey(),
                'bucketingKey' => $key->getBucketingKey()
            );
        } else {
            $strKey = \SplitIO\toString($key, 'key', $operation);
            if ($strKey && !empty($strKey)) {
                return array(
                    'matchingKey' => $strKey,
                    'bucketingKey' => null
                );
            } else {
                SplitApp::logger()->critical($operation . ': you passed ' . \SplitIO\converToString($key) .
                    ', key must be a non-empty string.');
            }
        }
        return null;
    }

    /**
     * @param $featureName
     * @return string|null
     */
    public static function validateFeatureName($featureName)
    {
        if (is_null($featureName) || !is_string($featureName) || empty($featureName)) {
            SplitApp::logger()->critical('getTreatment: you passed ' . \SplitIO\converToString($featureName) .
                ', split must be a non-empty string.');
            return null;
        }
        return $featureName;
    }

    /**
     * @param $key
     * @return string|null
     */
    public static function validateTrackKey($key)
    {
        if (is_null($key)) {
            SplitApp::logger()->critical("track: you passed 'null', key must be a non-empty string.");
            return null;
        }
        $strKey = \SplitIO\toString($key, 'key', 'track');
        if ($strKey && !empty($strKey)) {
            return $strKey;
        } else {
            SplitApp::logger()->critical('track: you passed ' . \SplitIO\converToString($key) .
                ', key must be a non-empty string.');
        }
        return null;
    }

    /**
     * @param $trafficType
     * @return string|null
     */
    public static function validateTrafficType($trafficType)
    {
        if (is_null($trafficType) || !is_string($trafficType) || empty($trafficType)) {
            SplitApp::logger()->critical('track: you passed ' . \SplitIO\converToString($trafficType) .
                ', trafficType must be a non-empty string.');
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
        if (is_null($eventType) || !is_string($eventType) || empty($eventType)) {
            SplitApp::logger()->critical('track: you passed ' . \SplitIO\converToString($eventType) .
                ', eventType must be a non-empty string.');
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
     * @param $featureNames
     * @return string|null
     */
    public static function validateManager($featureName)
    {
        if (is_null($featureName) || !is_string($featureName) || empty($featureName)) {
            SplitApp::logger()->critical('split: you passed ' . \SplitIO\converToString($featureName) .
            ', split must be a non-empty string.');
            return null;
        }
        return $featureName;
    }
}
