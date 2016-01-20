<?php
namespace SplitIO\Grammar\Condition\Matcher;


use SplitIO\Grammar\Condition\Matcher;

class All extends AbstractMatcher
{

    public function __construct($data, $negate = false)
    {
        parent::__construct(Matcher::ALL_KEYS, $negate);
    }
}