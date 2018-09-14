<?php

namespace SplitIO\Sdk\Validator;

use SplitIO\Split as SplitApp;

class InputValidator
{
    /**
     * @param $value
     * @param $name
     * @param $operation
     * @return true|false
     */
    private function checkIsString($value, $name, $operation)
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
    private function checkNotNull($value, $name, $operation)
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
    private function checkNotEmpty($value, $name, $operation)
    {
        if (empty($value)) {
            SplitApp::logger()->critical($operation . ': ' . $name . ' must not be an empty string.');
            return false;
        }
        return true;
    }

    /**
     * @param $key
     * @param $featureName
     * @return true|false
     */
    public static function validGetTreatmentInputs($key, $featureName)
    {
        if (!self::checkNotNull($key, 'key', 'getTreatment') ||
            !self::checkNotNull($featureName, 'featureName', 'getTreatment') ||
            !self::checkIsString($featureName, 'featureName', 'getTreatment')) {
            return false;
        }
        return true;
    }

    /**
     * @param $trafficType
     * @return true|false
     */
    private function isValidTrafficType($trafficType)
    {
        if (!self::checkNotNull($trafficType, 'trafficType', 'track') ||
            !self::checkIsString($trafficType, 'trafficType', 'track') ||
            !self::checkNotEmpty($trafficType, 'trafficType', 'track')) {
            return false;
        }
        return true;
    }

    /**
     * @param $eventType
     * @return true|false
     */
    private function isValidEventType($eventType)
    {
        if (!self::checkNotNull($eventType, 'eventType', 'track') ||
            !self::checkIsString($eventType, 'eventType', 'track') ||
            !self::checkNotEmpty($eventType, 'eventType', 'track')) {
            return false;
        }
        if (!preg_match('/[a-zA-Z0-9][-_\.a-zA-Z0-9]{0,62}/', $eventType)) {
            SplitApp::logger()->critical('track: eventType must adhere to the regular expression '
                . '[a-zA-Z0-9][-_\.a-zA-Z0-9]{0,62}.');
            return false;
        }
        return true;
    }

    /**
     * @param $value
     * @return true|false
     */
    private function isValidValue($value)
    {
        if (!self::checkNotNull($value, 'value', 'track')) {
            return false;
        }
        if (!(is_int($value) || is_float($value))) {
            SplitApp::logger()->critical('track: value must be a number.');
            return false;
        }
        return true;
    }

    /**
     * @param $key
     * @param $trafficType
     * @param $eventType
     * @param $value
     * @return true|false
     */
    public static function validTrackInputs($key, $trafficType, $eventType, $value)
    {
        if (!self::checkNotNull($key, 'key', 'track') ||
            !self::isValidTrafficType($trafficType) ||
            !self::isValidEventType($eventType) ||
            !self::isValidValue($value)) {
            return false;
        }
        return true;
    }

    /**
     * @param $featureName
     * @return true|false
     */
    public static function validManagerInputs($featureName)
    {
        if (!self::checkNotNull($featureName, 'featureName', 'split') ||
            !self::checkIsString($featureName, 'featureName', 'split')) {
            return false;
        }
        return true;
    }
}
