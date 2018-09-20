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
            SplitApp::logger()->critical($operation . ': ' . $name . ' ' .json_encode($value)
                . ' has to be of type "string".');
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
    public static function validateKey($key)
    {
        if (!self::checkNotNull($key, 'key', 'getTreatment')) {
            return null;
        }
        if ($key instanceof Key) {
            return array(
                'matchingKey' => $key->getMatchingKey(),
                'bucketingKey' => $key->getBucketingKey()
            );
        } else {
            $strKey = \SplitIO\toString($key, 'key', 'getTreatment');
            if ($strKey !== false) {
                return array(
                    'matchingKey' => $strKey,
                    'bucketingKey' => null
                );
            } else {
                SplitApp::logger()->critical('getTreatment: key has to be of type "string" or "SplitIO\Sdk\Key".');
                return null;
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
        return self::validateString($featureName, 'featureName', 'getTreatment');
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
        if ($strKey !== false) {
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
        if (!self::checkNotNull($value, 'value', 'track')) {
            return null;
        }
        if (!(is_int($value) || is_float($value))) {
            SplitApp::logger()->critical('track: value must be a number.');
            return null;
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
}
