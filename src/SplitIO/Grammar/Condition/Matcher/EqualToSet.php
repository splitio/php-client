<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Grammar\Condition\Matcher\DataType\Set;

class EqualToSet extends AbstractMatcher
{

    private $set;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::EQUAL_TO_SET, $negate, $attribute);
        $this->set = Set::fromArray($data);
    }

    protected function evalKey($key, array $context = null)
    {
        if (!is_array($key)) {
            return false;
        }

        SplitApp::logger()->info('---> Evaluating IS_EQUAL_TO_SET');
        SplitApp::logger()->info('---> Key elements: '.implode($key));
        SplitApp::logger()->info('---> Set elements: '.implode($this->set->toArray()));

        return $this->set->equals(Set::fromArray($key));
    }
}
