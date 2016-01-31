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

    protected $segmentData = null;

    public function __construct($data, $negate = false)
    {
        parent::__construct(Matcher::IN_SEGMENT, $negate);

        $this->userDefinedSegmentMatcherData = $data;

        $this->registerSegmentOnCache($data);

    }

    /**
     * Register the Segment Name on Cache
     * @param $segmentName
     */
    protected function registerSegmentOnCache($segmentName)
    {
        $cache = Di::getInstance()->getCache();

        $registeredSegmentsItem = $cache->getItem(\SplitIO\getCacheKeyForRegisterSegments());

        $segments = $registeredSegmentsItem->get();

        if ($segments) {
            $arraySegments = explode(',', $segments);
        } else {
            $arraySegments = [];
        }

        if (!in_array($segmentName, $arraySegments)) {
            $arraySegments[] = $segmentName;
            $registeredSegmentsItem->set(implode(',', $arraySegments));
            $registeredSegmentsItem->expiresAfter(Di::getInstance()->getSplitSdkConfiguration()->getCacheItemTtl());
            $cache->save($registeredSegmentsItem);
        }
    }

    /**
     * Fetch Segment Data from cache system. If not present on cache, force the fetch from server.
     * @return bool|SegmentData
     */
    protected function getSegmentData()
    {
        $cache = Di::getInstance()->getCache();
        $segmentName = $this->userDefinedSegmentMatcherData;
        $segmentDataCacheItem = $cache->getItem(\SplitIO\getCacheKeyForSegmentData($segmentName));

        if ($segmentDataCacheItem->isHit()) { //Update Segment Data.

            $segment = unserialize($segmentDataCacheItem->get());

            if ($segment instanceof SegmentData) {
                return $segment;
            }
        }

        return $this->getSegmentDataFromServer();
    }

    /**
     * @return bool|SegmentData
     */
    protected function getSegmentDataFromServer()
    {
        $segmentName = $this->userDefinedSegmentMatcherData;
        return Di::getInstance()->getSplitClient()->updateSegmentChanges($segmentName);
    }

    /**
     * @param $key
     * @return bool
     */
    protected function evalKey($key)
    {
        $this->segmentData = $this->getSegmentData();

        if ($this->segmentData) {

            foreach ($this->segmentData->getAddedUsers() as $validUser) {

                Di::getInstance()->getLogger()->info("Comparing: IN_SEGMENT - $key - $validUser");

                if ($key == $validUser) {

                    Di::getInstance()->getLogger()->info("User found: $key");

                    return true;
                }
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