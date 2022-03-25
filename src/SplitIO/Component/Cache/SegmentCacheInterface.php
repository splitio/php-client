<?php
namespace SplitIO\Component\Cache;

interface SegmentCacheInterface
{
    /**
     * @param $segmentName
     * @param $key
     * @return mixed
     */
    public function isInSegment($segmentName, $key);

    /**
     * @param $segmentName
     * @return mixed
     */
    public function getChangeNumber($segmentName);
}
