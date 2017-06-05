<?php
namespace SplitIO\Component\Cache;

use SplitIO\Component\Common\Di;

class BlockUntilReadyCache
{
    const KEY_CACHE_SPLITS_READY = 'SPLITIO.cache.splits.ready';

    const KEY_CACHE_SEGMENTS_READY = 'SPLITIO.cache.segments.ready';

    /**
     * @param $key
     * @param $val
     * @return bool
     */
    private function set($key, $val)
    {
        $item = Di::getCache()->getItem($key);
        $item->set($val);

        return Di::getCache()->save($item);
    }

    /**
     * @param $key
     * @return mixed
     */
    private function get($key)
    {
        $item = Di::getCache()->getItem($key);
        $checkpoint = $item->get();
        return (empty($checkpoint)) ? -1 : (int) $checkpoint;
    }

    public function setReadySplits($timestamp)
    {
        $this->set(self::KEY_CACHE_SPLITS_READY, $timestamp);
    }

    public function getReadySplits()
    {
        return $this->get(self::KEY_CACHE_SPLITS_READY);
    }

    public function getReadySegments()
    {
        return $this->get(self::KEY_CACHE_SEGMENTS_READY);
    }

    public function setReadySegments($timestamp)
    {
        $this->set(self::KEY_CACHE_SEGMENTS_READY, $timestamp);
    }

    /**
     * @return int
     */
    public function getReadyCheckpoint()
    {
        $splitsCheckpoint = $this->get(self::KEY_CACHE_SPLITS_READY);
        $segmentsCheckpoint = $this->get(self::KEY_CACHE_SEGMENTS_READY);
        return min($splitsCheckpoint, $segmentsCheckpoint);
    }
}
