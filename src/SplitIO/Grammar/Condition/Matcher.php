<?php
/**
 * Created by PhpStorm.
 * User: sarrubia
 * Date: 19/01/16
 * Time: 21:25
 */

namespace SplitIO\Grammar\Condition;


class Matcher
{

    const ALL_KEYS = 'ALL_KEYS';

    const IN_SEGMENT = 'IN_SEGMENT';

    const WHITELIST = 'WHITELIST';

    public static function factory($matcher)
    {
        $matcherType = $matcher['matcherType'];


        switch ($matcherType) {

            case self::ALL_KEYS:
                break;

            case self::IN_SEGMENT:
                break;

            case self::WHITELIST:
                break;
        }
    }
}