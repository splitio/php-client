<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Cache\SegmentCache;
use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Common\Di;

class Segment extends AbstractMatcher
{

    /**
     * For this version only will be the segment name.
     * @var array|string
     */
    protected $userDefinedSegmentMatcherData = null;

    protected $segmentData = null;

    public function __construct($data, $negate = false)
    {
        parent::__construct(Matcher::IN_SEGMENT, $negate);

        $this->userDefinedSegmentMatcherData = $data;

    }

    /**
     * @param $key
     * @return bool
     */
    protected function evalKey($key)
    {
        $segmentName = $this->userDefinedSegmentMatcherData;

        $segmentCache = new SegmentCache();

        if ($segmentCache->isInSegment($segmentName, $key)) {
            return true;
        }

        return false;
    }
}