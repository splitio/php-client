<?php
namespace SplitIO\Cache;

interface SplitCacheInterface
{
    /**
     * @param string $splitName
     * @param string $split JSON representation
     * @return boolean
     */
    public function addSplit($splitName, $split);

    /**
     * @param string $splitName
     * @return boolean
     */
    public function removeSplit($splitName);

    /**
     * @param long $changeNumber
     * @return boolean
     */
    public function setChangeNumber($changeNumber);

    /**
     * @return long
     */
    public function getChangeNumber();

    /**
     * @param string $splitName
     * @return string JSON representation
     */
    public function getSplit($splitName);
}
