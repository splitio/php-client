<?php

namespace SplitIO\Sdk\Validator;

use SplitIO\Split as SplitApp;
use SplitIO\Sdk\Key;

class InputValidator
{
    /**
     * @param $value
     * @param $name
     * @param $operation
     * @return true|false
     */
    private static function checkIsString($value, $name, $operation)
    {
        if (!is_string($value)) {
            SplitApp::logger()->critical($operation . ': ' . $name . ' has to be of type "string".');
            return false;
        }
        return true;
    }

    /**
     * @param $value
     * @param $name
     * @param $operation
     * @return true|false
     */
    private static function checkNotNull($value, $name, $operation)
    {
        if (is_null($value)) {
            SplitApp::logger()->critical($operation . ': ' . $name . ' cannot be null.');
            return false;
        }
        return true;
    }

    /**
     * @param $value
     * @param $name
     * @param $operation
     * @return true|false
     */
    private static function checkNotEmpty($value, $name, $operation)
    {
        if (empty($value)) {
            SplitApp::logger()->critical($operation . ': ' . $name . ' must not be an empty string.');
            return false;
        }
        return true;
    }

    /**
     * @param $value
     * @param $name
     * @param $operation
     * @return string|null
     */
    private static function validateString($value, $name, $operation)
    {
        if (!self::checkNotNull($value, $name, $operation) ||
            !self::checkIsString($value, $name, $operation)) {
            return null;
        }
        return $value;
    }

    /**
     * @param $value
     * @param $name
     * @param $operation
     * @return string|null
     */
    private static function validateStringParameter($value, $name, $operation)
    {
        if (is_null(self::validateString($value, $name, $operation)) ||
            !self::checkNotEmpty($value, $name, $operation)) {
            return null;
        }
        return $value;
    }

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
        if (!self::checkNotNull($key, 'key', 'track')) {
            return null;
        }
        $strKey = \SplitIO\toString($key, 'key', 'track');
        if ($strKey) {
            return $strKey;
        } else {
            SplitApp::logger()->critical('track: key ' .json_encode($key)
                . ' has to be of type "string".');
        }
        return null;
    }

    /**
     * @param $trafficType
     * @return string|null
     */
    public static function validateTrafficType($trafficType)
    {
        return self::validateStringParameter($trafficType, 'trafficType', 'track');
    }

    /**
     * @param $eventType
     * @return string|null
     */
    public static function validateEventType($eventType)
    {
        if (is_null(self::validateStringParameter($eventType, 'eventType', 'track'))) {
            return null;
        }
        if (!preg_match('/[a-zA-Z0-9][-_\.a-zA-Z0-9]{0,62}/', $eventType)) {
            SplitApp::logger()->critical('track: eventType must adhere to the regular expression '
                . '[a-zA-Z0-9][-_\.a-zA-Z0-9]{0,62}.');
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
     * @return string|null
     */
    public static function validateSplitFeatureName($featureName)
    {
        return self::validateString($featureName, 'featureName', 'split');
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
}
