<?php

namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;

class Whitelist extends AbstractMatcher
{
    protected $whitelistMatcherData = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::WHITELIST, $negate, $attribute);

        $this->whitelistMatcherData = $data;
    }

    protected function evalKey($key, array $context = null)
    {
        return (is_array($this->whitelistMatcherData)) ? in_array($key, $this->whitelistMatcherData) : false;
    }
}
