<?php
namespace SplitIO\Grammar\Condition\Matcher;


use SplitIO\Common\Di;
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

            Di::getInstance()->getLogger()->info("Comparing: WHITELIST - $userId - $whiteListedUser");

            if ($userId == $whiteListedUser) {

                Di::getInstance()->getLogger()->info("User found: $userId");

                return true;
            }

        }

        return false;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->whitelistMatcherData;
    }
}