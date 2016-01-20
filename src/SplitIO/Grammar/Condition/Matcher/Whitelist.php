<?php
namespace SplitIO\Grammar\Condition\Matcher;


use SplitIO\Grammar\Condition\Matcher;

class Whitelist extends AbstractMatcher
{
    protected $whitelistMatcherData = null;

    public function __construct($data, $negate = false)
    {
        parent::__construct(Matcher::WHITELIST, $negate);

        $this->whitelistMatcherData = $data;
    }
}