<?php

namespace SplitIO\Sdk\Validator;

use SplitIO\Component\Utils as SplitIOUtils;
use SplitIO\Split as SplitApp;

const REG_EXP_FLAG_SET = "/^[a-z0-9][_a-z0-9]{0,49}$/";

class FlagSetsValidator
{
    public static function areValid(array $flagSets, string $operation)
    {
        if (!is_array($flagSets) || SplitIOUtils\isAssociativeArray($flagSets) || count($flagSets) == 0) {
            SplitApp::logger()->error($operation . ': FlagSets must be a non-empty list.');
            return array();
        }

        $sanitized = [];
        foreach ($flagSets as $flagSet) {
            if ($flagSet == null) {
                continue;
            }

            if (!is_string($flagSet)) {
                SplitApp::logger()->error($operation . ': FlagSet must be a string and not null. ' .
                $flagSet . ' was discarded.');
                continue;
            }
            $sanitizedFlagSet = self::sanitize($flagSet, $operation);
            if (!is_null($sanitizedFlagSet)) {
                array_push($sanitized, $sanitizedFlagSet);
            }
        }

        return array_values(array_unique($sanitized));
    }

    private static function sanitize(string $flagSet, string $operation)
    {
        $trimmed = trim($flagSet);
        if ($trimmed !== $flagSet) {
            SplitApp::logger()->warning($operation . ': Flag Set name "' . $flagSet .
                '" has extra whitespace, trimming.');
        }
        $toLowercase = strtolower($trimmed);
        if ($toLowercase !== $trimmed) {
            SplitApp::logger()->warning($operation . ': Flag Set name "' . $flagSet .
                '" should be all lowercase - converting string to lowercase.');
        }
        if (!preg_match(REG_EXP_FLAG_SET, $toLowercase)) {
            SplitApp::logger()->warning($operation . ': you passed "' . $flagSet .
                '", Flag Set must adhere to the regular expressions {' .REG_EXP_FLAG_SET .
                '} This means a Flag Set must start with a letter or number, be in lowercase, alphanumeric and ' .
                'have a max length of 50 characters. "' . $flagSet . '" was discarded.');
            return null;
        }

        return $toLowercase;
    }
}
