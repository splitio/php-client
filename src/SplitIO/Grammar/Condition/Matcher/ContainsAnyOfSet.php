<?php

namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Grammar\Condition\Matcher\DataType\Set;

class ContainsAnyOfSet extends AbstractMatcher
{
    private $set;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::CONTAINS_ANY_OF_SET, $negate, $attribute);
        $this->set = Set::fromArray($data);
    }

    protected function evalKey($key, array $context = null)
    {
        if (!is_array($key)) {
            return false;
        }

        SplitApp::logger()->info('---> Evaluating CONTAINS_ANY_OF_SET');
        SplitApp::logger()->info('---> Key elements: ' . implode($key));
        SplitApp::logger()->info('---> Set elements: ' . implode($this->set->toArray()));

        return $this->set->intersect(Set::fromArray($key))->count() > 0;
    }
}
