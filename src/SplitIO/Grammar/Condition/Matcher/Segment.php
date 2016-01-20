<?php
namespace SplitIO\Grammar\Condition\Matcher;


use SplitIO\Grammar\Condition\Matcher;

class Segment extends AbstractMatcher
{
    protected $userDefinedSegmentMatcherData = null;

    public function __construct($data, $negate = false)
    {
        parent::__construct(Matcher::IN_SEGMENT, $negate);

        $this->userDefinedSegmentMatcherData = $data;
    }
}