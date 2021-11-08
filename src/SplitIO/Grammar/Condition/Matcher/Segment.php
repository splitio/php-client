<?php
namespace SplitIO\Grammar\Condition\Matcher;

use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Engine\Hash\Murmur3Hash;
use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Split as SplitApp;

class Segment extends AbstractMatcher
{
    /**
     * For this version only will be the segment name.
     * @var array|string
     */
    protected $userDefinedSegmentMatcherData = null;

    /**
     * @var null
     */
    protected $segmentData = null;

    /**
     * @param $data
     * @param bool|false $negate
     */
    public function __construct($data, $negate = false, $attribute = null)
    {
        parent::__construct(Matcher::IN_SEGMENT, $negate, $attribute);

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

    /**
     * @param $segmentName
     * @param $key
     * @return mixed
     */
    private function getSmKey($segmentName, $key)
    {
        $murmurHashFn = new Murmur3Hash();
        return $murmurHashFn->getHash("segment::".$segmentName."::".$key, $this->smKeySeed);
    }
}
