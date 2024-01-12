<?php
namespace SplitIO\Component\Cache;

interface SplitCacheInterface
{
    /**
     * @return long
     */
    public function getChangeNumber();

    /**
     * @param string $splitName
     * @return string JSON representation
     */
    public function getSplit($splitName);

    /**
     * @param array(string) List of flag set names
     * @return array(string) List of all feature flag names by flag sets
     */
    public function getNamesByFlagSets($flagSets);
}
