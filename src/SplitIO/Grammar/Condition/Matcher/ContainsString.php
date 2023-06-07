<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;

class ContainsString extends AbstractMatcher
{
    private $containsStringMatcherData = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::CONTAINS_STRING, $negate, $attribute);

        $this->containsStringMatcherData = $data;
    }

    protected function evalKey($key, array $context = null)
    {
        if (!is_array($this->containsStringMatcherData) || !is_string($key) || strlen($key) == 0) {
            return false;
        }

        foreach ($this->containsStringMatcherData as $item) {
            if (is_string($item) && strpos($key, $item) !== false) {
                return true;
            }
        }
    
        return false;
    }
}
