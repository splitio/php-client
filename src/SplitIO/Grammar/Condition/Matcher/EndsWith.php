<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;

class EndsWith extends AbstractMatcher
{
    protected $endsWithMatcherData = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::ENDS_WITH, $negate, $attribute);

        $this->endsWithMatcherData = $data;
    }

    protected function evalKey($key)
    {
        if (!is_array($this->endsWithMatcherData)) {
            return false;
        }

        // TODO: Review this case with @sarrubia
        $keyLength = strlen($key);
        if ($keyLength === 0) {
            return true;
        }

        foreach ($this->endsWithMatcherData as $item) {
            if (is_string($item) && substr($item, -$keyLength) == $key) {
                return true;
            }
        }
        return false;
    }
}
