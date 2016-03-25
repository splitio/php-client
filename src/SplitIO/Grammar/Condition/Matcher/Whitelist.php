<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;

class Whitelist extends AbstractMatcher
{
    protected $whitelistMatcherData = null;

    public function __construct($data, $negate = false)
    {
        parent::__construct(Matcher::WHITELIST, $negate);

        $this->whitelistMatcherData = $data;
    }

    protected function evalKey($key)
    {
        return (is_array($this->whitelistMatcherData)) ? in_array($key, $this->whitelistMatcherData) : false;
    }
}
