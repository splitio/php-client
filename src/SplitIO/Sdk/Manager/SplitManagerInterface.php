<?php

namespace SplitIO\Sdk\Manager;

interface SplitManagerInterface
{
    /**
     * @return array
     */
    public function splitNames() :array;

    /**
     * @return array
     */
    public function splits() :array;

    /**
     * @param string $featureFlagName
     * @return \SplitIO\Sdk\Manager\SplitView
     */
    public function split(string $featureFlagName): ?SplitView;
}
