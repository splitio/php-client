<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Matcher;

class Whitelist extends AbstractMatcher
{
    protected $whitelistMatcherData = null;

    public function __construct($data, $negate = false)
    {
        parent::__construct(Matcher::WHITELIST, $negate);

        $this->whitelistMatcherData = $data;
    }

    protected function evalKey($userId)
    {
        foreach ($this->whitelistMatcherData as $whiteListedUser) {
            SplitApp::logger()->info("Comparing: WHITELIST - $userId - $whiteListedUser");

            if ($userId == $whiteListedUser) {
                SplitApp::logger()->info("User found: $userId");

                return true;
            }
        }

        return false;
    }
}
