<?php
/**
 * Created by PhpStorm.
 * User: sarrubia
 * Date: 19/01/16
 * Time: 21:35
 */

namespace SplitIO\Grammar\Condition\Matcher;

/*
{
"matcherType": "ALL_KEYS",
"negate": false,
"userDefinedSegmentMatcherData": null,
"whitelistMatcherData": null
}
*/
abstract class AbstractMatcher
{
    protected $type = null;

    protected $negate = false;

    protected function __construct($type, $negate=false)
    {
        $this->type = $type;

        $this->negate = $negate;
    }

}