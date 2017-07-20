<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;

class EqualToBoolean extends AbstractMatcher
{
    protected $booleanMatcherData = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::EQUAL_TO_BOOLEAN, $negate, $attribute);

        $this->booleanMatcherData = $data;
    }

    protected function evalKey($key)
    {
        return (is_bool($this->booleanMatcherData) && $this->booleanMatcherData);
    }
}
