<?php
namespace SplitIO\Grammar\Condition;

use SplitIO\Exception\UnsupportedMatcherException;
use SplitIO\Grammar\Condition\Matcher\All;
use SplitIO\Grammar\Condition\Matcher\Between;
use SplitIO\Grammar\Condition\Matcher\EqualTo;
use SplitIO\Grammar\Condition\Matcher\GreaterThanOrEqualTo;
use SplitIO\Grammar\Condition\Matcher\LessThanOrEqualTo;
use SplitIO\Grammar\Condition\Matcher\Segment;
use SplitIO\Grammar\Condition\Matcher\Whitelist;
use SplitIO\Grammar\Condition\Matcher\StartsWith;
use SplitIO\Grammar\Condition\Matcher\EndsWith;
use SplitIO\Grammar\Condition\Matcher\ContainsString;
use SplitIO\Grammar\Condition\Matcher\ContainsAllOfSet;
use SplitIO\Grammar\Condition\Matcher\ContainsAnyOfSet;
use SplitIO\Grammar\Condition\Matcher\EqualToSet;
use SplitIO\Grammar\Condition\Matcher\PartOfSet;
use SplitIO\Grammar\Condition\Matcher\Dependency;
use SplitIO\Grammar\Condition\Matcher\EqualToBoolean;
use SplitIO\Grammar\Condition\Matcher\Regex;

class Matcher
{

    const ALL_KEYS = 'ALL_KEYS';
    const IN_SEGMENT = 'IN_SEGMENT';
    const WHITELIST = 'WHITELIST';
    const EQUAL_TO = 'EQUAL_TO';
    const GREATER_THAN_OR_EQUAL_TO = 'GREATER_THAN_OR_EQUAL_TO';
    const LESS_THAN_OR_EQUAL_TO = 'LESS_THAN_OR_EQUAL_TO';
    const BETWEEN = 'BETWEEN';
    const STARTS_WITH = 'STARTS_WITH';
    const ENDS_WITH = 'ENDS_WITH';
    const CONTAINS_STRING = 'CONTAINS_STRING';
    const CONTAINS_ALL_OF_SET = 'CONTAINS_ALL_OF_SET';
    const CONTAINS_ANY_OF_SET = 'CONTAINS_ANY_OF_SET';
    const EQUAL_TO_SET = 'EQUAL_TO_SET';
    const PART_OF_SET = 'PART_OF_SET';
    const IN_SPLIT_TREATMENT = 'IN_SPLIT_TREATMENT';
    const EQUAL_TO_BOOLEAN = 'EQUAL_TO_BOOLEAN';
    const MATCHES_STRING = 'MATCHES_STRING';

    public static function factory($matcher)
    {
        $matcherType = $matcher['matcherType'];
        $negate = (isset($matcher['negate']) && is_bool($matcher['negate'])) ? $matcher['negate'] : false;
        $attribute = (isset($matcher['keySelector']['attribute'])) ? $matcher['keySelector']['attribute'] : null;

        switch ($matcherType) {
            case self::ALL_KEYS:
                return new All($negate, $attribute);
            case self::IN_SEGMENT:
                $data = (isset($matcher['userDefinedSegmentMatcherData']['segmentName']) &&
                            is_string($matcher['userDefinedSegmentMatcherData']['segmentName']))
                            ? $matcher['userDefinedSegmentMatcherData']['segmentName'] : null;
                return new Segment($data, $negate, $attribute);
            case self::WHITELIST:
                $data = (isset($matcher['whitelistMatcherData']['whitelist']) &&
                    is_array($matcher['whitelistMatcherData']['whitelist']))
                    ? $matcher['whitelistMatcherData']['whitelist'] : null;
                return new Whitelist($data, $negate, $attribute);
            case self::EQUAL_TO:
                $data = (isset($matcher['unaryNumericMatcherData']) &&
                    is_array($matcher['unaryNumericMatcherData']))
                    ? $matcher['unaryNumericMatcherData'] : null;
                return new EqualTo($data, $negate, $attribute);
            case self::GREATER_THAN_OR_EQUAL_TO:
                $data = (isset($matcher['unaryNumericMatcherData']) &&
                    is_array($matcher['unaryNumericMatcherData']))
                    ? $matcher['unaryNumericMatcherData'] : null;
                return new GreaterThanOrEqualTo($data, $negate, $attribute);
            case self::LESS_THAN_OR_EQUAL_TO:
                $data = (isset($matcher['unaryNumericMatcherData']) &&
                    is_array($matcher['unaryNumericMatcherData']))
                    ? $matcher['unaryNumericMatcherData'] : null;
                return new LessThanOrEqualTo($data, $negate, $attribute);
            case self::BETWEEN:
                $data = (isset($matcher['betweenMatcherData']) &&
                    is_array($matcher['betweenMatcherData']))
                    ? $matcher['betweenMatcherData'] : null;
                return new Between($data, $negate, $attribute);
            case self::STARTS_WITH:
                $data = (isset($matcher['whitelistMatcherData']['whitelist']) &&
                    is_array($matcher['whitelistMatcherData']['whitelist']))
                    ? $matcher['whitelistMatcherData']['whitelist'] : null;
                return new StartsWith($data, $negate, $attribute);
            case self::ENDS_WITH:
                $data = (isset($matcher['whitelistMatcherData']['whitelist']) &&
                    is_array($matcher['whitelistMatcherData']['whitelist']))
                    ? $matcher['whitelistMatcherData']['whitelist'] : null;
                return new EndsWith($data, $negate, $attribute);
            case self::CONTAINS_STRING:
                $data = (isset($matcher['whitelistMatcherData']['whitelist']) &&
                    is_array($matcher['whitelistMatcherData']['whitelist']))
                    ? $matcher['whitelistMatcherData']['whitelist'] : null;
                return new ContainsString($data, $negate, $attribute);
            case self::CONTAINS_ALL_OF_SET:
                $data = (isset($matcher['whitelistMatcherData']['whitelist']) &&
                    is_array($matcher['whitelistMatcherData']['whitelist']))
                    ? $matcher['whitelistMatcherData']['whitelist'] : null;
                return new ContainsAllOfSet($data, $negate, $attribute);
            case self::CONTAINS_ANY_OF_SET:
                $data = (isset($matcher['whitelistMatcherData']['whitelist']) &&
                    is_array($matcher['whitelistMatcherData']['whitelist']))
                    ? $matcher['whitelistMatcherData']['whitelist'] : null;
                return new ContainsAnyOfSet($data, $negate, $attribute);
            case self::EQUAL_TO_SET:
                $data = (isset($matcher['whitelistMatcherData']['whitelist']) &&
                    is_array($matcher['whitelistMatcherData']['whitelist']))
                    ? $matcher['whitelistMatcherData']['whitelist'] : null;
                return new EqualToSet($data, $negate, $attribute);
            case self::PART_OF_SET:
                $data = (isset($matcher['whitelistMatcherData']['whitelist']) &&
                    is_array($matcher['whitelistMatcherData']['whitelist']))
                    ? $matcher['whitelistMatcherData']['whitelist'] : null;
                return new PartOfSet($data, $negate, $attribute);
            case self::IN_SPLIT_TREATMENT:
                $data = isset($matcher['dependencyMatcherData']) &&
                    is_array($matcher['dependencyMatcherData']) ?
                    $matcher['dependencyMatcherData'] : null;
                return new Dependency($data, $negate, $attribute);
            case self::EQUAL_TO_BOOLEAN:
                $data = isset($matcher['booleanMatcherData'])
                    && is_bool($matcher['booleanMatcherData']) ?
                    $matcher['booleanMatcherData'] : null;
                return new EqualToBoolean($data, $negate, $attribute);
            case self::MATCHES_STRING:
                $data = isset($matcher['stringMatcherData']) &&
                    is_string($matcher['stringMatcherData']) ?
                    $matcher['stringMatcherData'] : null;
                return new Regex($data, $negate, $attribute);
            // @codeCoverageIgnoreStart
            default:
                throw new UnsupportedMatcherException("Unable to create matcher for matcher type: " . $matcherType);
        }
        // @codeCoverageIgnoreEnd
    }
}
