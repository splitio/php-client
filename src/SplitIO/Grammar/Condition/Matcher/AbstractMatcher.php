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
use SplitIO\Common\Di;

abstract class AbstractMatcher
{
    protected $type = null;

    protected $negate = false;

    protected function __construct($type, $negate = false)
    {
        Di::getInstance()->getLogger()->info("Constructing matcher of type: ".$type);

        $this->type = $type;

        $this->negate = $negate;
    }

    public function evaluate($userId)
    {
        Di::getInstance()->getLogger()->info("Evaluating on {$this->type} the userID $userId");

        $evaluation =  $this->_eval($userId);
        return ($this->negate) ? !$evaluation : $evaluation;
    }

    abstract protected function _eval($userId);

    abstract public function getUsers();
}