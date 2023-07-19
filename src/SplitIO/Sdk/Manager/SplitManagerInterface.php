<?php
namespace SplitIO\Sdk\Manager;

interface SplitManagerInterface
{
    /**
     * @return array
     */
    public function splitNames();

    /**
     * @return array
     */
    public function splits();

    /**
     * @param $featureFlagName
     * @return \SplitIO\Sdk\Manager\SplitView
     */
    public function split($featureFlagName);
}
