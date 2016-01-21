<?php
namespace SplitIO\Grammar\Condition;

use SplitIO\Grammar\Condition\Matcher\All;
use SplitIO\Grammar\Condition\Matcher\Segment;
use SplitIO\Grammar\Condition\Matcher\Whitelist;
use SplitIO\Common\Di;

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
                Di::getInstance()->getLogger()->debug("Creating Matcher: ALL_KEYS");
                return new All($negate);
                break;

            case self::IN_SEGMENT:
                Di::getInstance()->getLogger()->debug("Creating Matcher: IN_SEGMENT");
                $data = (isset($matcher['userDefinedSegmentMatcherData']) &&
                            is_bool($matcher['userDefinedSegmentMatcherData']))
                            ? $matcher['userDefinedSegmentMatcherData'] : null;
                return new Segment($data, $negate);
                break;

            case self::WHITELIST:
                Di::getInstance()->getLogger()->debug("Creating Matcher: WHITELIST");
                $data = (isset($matcher['whitelistMatcherData']) &&
                    is_bool($matcher['whitelistMatcherData']))
                    ? $matcher['whitelistMatcherData'] : null;
                return new Whitelist($data, $negate);
                break;

            default:
                return null;
        }
    }
}