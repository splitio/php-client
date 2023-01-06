<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;

class EqualToBoolean extends AbstractMatcher
{
    protected $booleanMatcherData = null;

    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::EQUAL_TO_BOOLEAN, $negate, $attribute);

        $this->booleanMatcherData = $data;
    }

    protected function evalKey($key, array $context = null)
    {
        if (is_string($key)) {
            $decodedKey = json_decode(strtolower($key));
        } elseif (is_bool($key)) {
            $decodedKey = $key;
        } else {
            return false;
        }

        if (!is_bool($decodedKey)) {
            return false;
        }

        $castedMatcherData = ((bool) $this->booleanMatcherData);
        return ($decodedKey == $castedMatcherData);
    }
}
