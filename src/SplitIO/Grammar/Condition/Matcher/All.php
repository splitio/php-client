<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;

class All extends AbstractMatcher
{

    public function __construct($data, $negate = false)
    {
        parent::__construct(Matcher::ALL_KEYS, $negate);
    }

    protected function evalKey($key)
    {
        SplitApp::logger()->info("Comparing: ALL_KEYS - $key");
        SplitApp::logger()->info("User found: $key");
        return true;
    }
}
