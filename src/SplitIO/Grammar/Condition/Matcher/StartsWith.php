<?php

namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;

class StartsWith extends AbstractMatcher
{
    protected $startsWithMatcherData = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::STARTS_WITH, $negate, $attribute);

        $this->startsWithMatcherData = $data;
    }

    protected function evalKey($key, array $context = null)
    {
        if (!is_array($this->startsWithMatcherData) || !is_string($key) || strlen($key) == 0) {
            return false;
        }

        foreach ($this->startsWithMatcherData as $item) {
            if (is_string($item) && substr($key, 0, strlen($item)) == $item) {
                return true;
            }
        }
        return false;
    }
}
