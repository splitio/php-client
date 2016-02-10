<?php
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

    public function evaluate($key)
    {
        Di::getInstance()->getLogger()->info("Evaluating on {$this->type} the KEY $key");

        return $this->evalKey($key);
    }

    public function isNegate()
    {
        return $this->negate;
    }

    abstract protected function evalKey($key);
}
