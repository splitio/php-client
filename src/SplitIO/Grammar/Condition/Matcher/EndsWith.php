<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;

class EndsWith extends AbstractMatcher
{
    protected $endsWithMatcherData = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::ENDS_WITH, $negate, $attribute);

        $this->endsWithMatcherData = $data;
    }

    protected function evalKey($key, array $context = null)
    {
        if (!is_array($this->endsWithMatcherData) || !is_string($key) || strlen($key) == 0) {
            return false;
        }

        foreach ($this->endsWithMatcherData as $item) {
            if (is_string($item) && substr($key, -strlen($item)) == $item) {
                return true;
            }
        }
        return false;
    }
}
