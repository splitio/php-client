<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;

class Regex extends AbstractMatcher
{
    protected $regexMatcherData = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::MATCHES_STRING, $negate, $attribute);

        $this->regexMatcherData = $data;
    }

    protected function evalKey($key)
    {
        return (is_string($this->regexMatcherData) &&
            preg_match('/' . str_replace('\/', '\\/', $this->regexMatcherData) . '/', $key));
    }
}
