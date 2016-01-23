<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Common\Di;

class Segment extends AbstractMatcher
{
    /**
     * For this version only will be the segment name.
     * @var array|string
     */
    protected $userDefinedSegmentMatcherData = null;

    protected $addedUsers = null;

    protected $removedUsers = null;

    protected $since = -1;

    protected $till = null;

    public function __construct($data, $negate = false)
    {
        parent::__construct(Matcher::IN_SEGMENT, $negate);

        $this->userDefinedSegmentMatcherData = $data;

        $_segmentData = json_decode(Di::getInstance()->getSplitClient()->getSegmentChanges($data), true);

        if ($_segmentData) {

            $this->addedUsers = (isset($_segmentData['added'])) ? $_segmentData['added'] : [];
            $this->removedUsers = (isset($_segmentData['removed'])) ? $_segmentData['removed'] : [];
            $this->since = (isset($_segmentData['since'])) ? $_segmentData['since'] : -1;
            $this->till = (isset($_segmentData['till'])) ? $_segmentData['till'] : mktime();
        }

    }

    protected function _eval($userId)
    {
        foreach ($this->addedUsers as $validUser) {

            Di::getInstance()->getLogger()->info("Comparing: IN_SEGMENT - $userId - $validUser");

            if ($userId == $validUser) {

                Di::getInstance()->getLogger()->info("User found: $userId");

                return true;
            }
        }

        return false;
    }

    /**
     * @return array|null
     */
    public function getUsers()
    {
        return $this->addedUsers;
    }
}