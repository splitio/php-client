<?php
namespace SplitIO\Grammar\Condition;

use SplitIO\Grammar\Condition\Matcher\All;
use SplitIO\Grammar\Condition\Matcher\Between;
use SplitIO\Grammar\Condition\Matcher\EqualTo;
use SplitIO\Grammar\Condition\Matcher\GreaterThanOrEqualTo;
use SplitIO\Grammar\Condition\Matcher\LessThanOrEqualTo;
use SplitIO\Grammar\Condition\Matcher\Segment;
use SplitIO\Grammar\Condition\Matcher\Whitelist;

class Matcher
{

    const ALL_KEYS = 'ALL_KEYS';

    const IN_SEGMENT = 'IN_SEGMENT';

    const WHITELIST = 'WHITELIST';

    const EQUAL_TO = 'EQUAL_TO';

    const GREATER_THAN_OR_EQUAL_TO = 'GREATER_THAN_OR_EQUAL_TO';

    const LESS_THAN_OR_EQUAL_TO = 'LESS_THAN_OR_EQUAL_TO';

    const BETWEEN = 'BETWEEN';


    public static function factory($matcher)
    {
        $matcherType = $matcher['matcherType'];
        $negate = (isset($matcher['negate']) && is_bool($matcher['negate'])) ? $matcher['negate'] : false;
        $attribute = (isset($matcher['keySelector']['attribute'])) ? $matcher['keySelector']['attribute'] : null;

        switch ($matcherType) {

            case self::ALL_KEYS:
                return new All($negate, $attribute);
                break;

            case self::IN_SEGMENT:
                $data = (isset($matcher['userDefinedSegmentMatcherData']['segmentName']) &&
                            is_string($matcher['userDefinedSegmentMatcherData']['segmentName']))
                            ? $matcher['userDefinedSegmentMatcherData']['segmentName'] : null;
                return new Segment($data, $negate, $attribute);
                break;

            case self::WHITELIST:
                $data = (isset($matcher['whitelistMatcherData']['whitelist']) &&
                    is_array($matcher['whitelistMatcherData']['whitelist']))
                    ? $matcher['whitelistMatcherData']['whitelist'] : null;
                return new Whitelist($data, $negate, $attribute);
                break;

            case self::EQUAL_TO:
                $data = (isset($matcher['unaryNumericMatcherData']) &&
                    is_array($matcher['unaryNumericMatcherData']))
                    ? $matcher['unaryNumericMatcherData'] : null;
                return new EqualTo($data, $negate, $attribute);
                break;

            case self::GREATER_THAN_OR_EQUAL_TO:
                $data = (isset($matcher['unaryNumericMatcherData']) &&
                    is_array($matcher['unaryNumericMatcherData']))
                    ? $matcher['unaryNumericMatcherData'] : null;
                return new GreaterThanOrEqualTo($data, $negate, $attribute);
                break;

            case self::LESS_THAN_OR_EQUAL_TO:
                $data = (isset($matcher['unaryNumericMatcherData']) &&
                    is_array($matcher['unaryNumericMatcherData']))
                    ? $matcher['unaryNumericMatcherData'] : null;
                return new LessThanOrEqualTo($data, $negate, $attribute);
                break;

            case self::BETWEEN:
                $data = (isset($matcher['betweenMatcherData']) &&
                    is_array($matcher['betweenMatcherData']))
                    ? $matcher['betweenMatcherData'] : null;
                return new Between($data, $negate, $attribute);
                break;

            // @codeCoverageIgnoreStart
            default:
                return null;
        }
        // @codeCoverageIgnoreEnd
    }
}
