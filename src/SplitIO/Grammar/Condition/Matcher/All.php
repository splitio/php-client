<?php
namespace SplitIO\Grammar\Condition\Matcher;


use SplitIO\Common\Di;
use SplitIO\Grammar\Condition\Matcher;

class All extends AbstractMatcher
{

    public function __construct($data, $negate = false)
    {
        parent::__construct(Matcher::ALL_KEYS, $negate);
    }

    protected function evalKey($key)
    {
        Di::getInstance()->getLogger()->info("Comparing: ALL_KEYS - $key");
        Di::getInstance()->getLogger()->info("User found: $key");
        return true;
    }
}