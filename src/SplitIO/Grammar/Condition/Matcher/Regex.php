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
        if (!is_string($this->regexMatcherData)) {
            return false;
        }

        // If there are already escaped forwarded slashes, unescape them so that they don't get
        // escaped twice
        $unescaped = str_replace('\\/', '/', $this->regexMatcherData);
        
        // Escape ALL forward slashes.
        $reEscaped = str_replace('/', '\\/', $unescaped);

        $res = preg_match('/' . $reEscaped . '/', $key);
        if ($res == 1) {
            return true;
        }
        return false;
    }
}
