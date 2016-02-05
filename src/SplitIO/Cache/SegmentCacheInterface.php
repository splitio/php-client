<?php
namespace SplitIO\Cache;

interface SegmentCacheInterface
{
    /**
     * @param $segmentName
     * @param $segmentKeys
     * @return mixed
     */
    public function addToSegment($segmentName, array $segmentKeys);

    /**
     * @param $segmentName
     * @param array $segmentKeys
     * @return mixed
     */
    public function removeFromSegment($segmentName, array $segmentKeys);

    /**
     * @param $segmentName
     * @param $key
     * @return mixed
     */
    public function isInSegment($segmentName, $key);

    /**
     * @param $segmentName
     * @param $changeNumber
     * @return mixed
     */
    public function setChangeNumber($segmentName, $changeNumber);

    /**
     * @param $segmentName
     * @return mixed
     */
    public function getChangeNumber($segmentName);
}