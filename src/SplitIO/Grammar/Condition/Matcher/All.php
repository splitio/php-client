<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;

class All extends AbstractMatcher
{

    public function __construct($negate = false, $attribute = null)
    {
        parent::__construct(Matcher::ALL_KEYS, $negate, $attribute);
    }

    protected function evalKey($key)
    {
        SplitApp::logger()->info("Comparing: ALL_KEYS - $key");
        SplitApp::logger()->info("User found: $key");
        return true;
    }
}
