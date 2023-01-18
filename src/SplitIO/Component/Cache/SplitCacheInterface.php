<?php
namespace SplitIO\Component\Cache;

interface SplitCacheInterface
{
    /**
     * @return int
     */
    public function getChangeNumber();

    /**
     * @param string $splitName
     * @return string JSON representation
     */
    public function getSplit($splitName);
}
