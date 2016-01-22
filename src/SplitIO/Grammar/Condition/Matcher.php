<?php
namespace SplitIO\Grammar\Condition;

use SplitIO\Grammar\Condition\Matcher\All;
use SplitIO\Grammar\Condition\Matcher\Segment;
use SplitIO\Grammar\Condition\Matcher\Whitelist;

class Matcher
{

    const ALL_KEYS = 'ALL_KEYS';

    const IN_SEGMENT = 'IN_SEGMENT';

    const WHITELIST = 'WHITELIST';

    public static function factory($matcher)
    {
        $matcherType = $matcher['matcherType'];
        $negate = (isset($matcher['negate']) && is_bool($matcher['negate'])) ? $matcher['negate'] : false;

        switch ($matcherType) {

            case self::ALL_KEYS:
                return new All($negate);
                break;

            case self::IN_SEGMENT:
                $data = (isset($matcher['userDefinedSegmentMatcherData']['segmentName']) &&
                            is_string($matcher['userDefinedSegmentMatcherData']['segmentName']))
                            ? $matcher['userDefinedSegmentMatcherData']['segmentName'] : null;
                return new Segment($data, $negate);
                break;

            case self::WHITELIST:
                $data = (isset($matcher['whitelistMatcherData']['whitelist']) &&
                    is_array($matcher['whitelistMatcherData']['whitelist']))
                    ? $matcher['whitelistMatcherData']['whitelist'] : null;
                return new Whitelist($data, $negate);
                break;

            default:
                return null;
        }
    }
}