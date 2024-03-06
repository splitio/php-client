<?php

namespace SplitIO\Sdk\Factory;

interface SplitFactoryInterface
{
    /**
     * @return \SplitIO\Sdk\ClientInterface
     */
    public function client(): \SplitIO\Sdk\ClientInterface;

    /**
     * @return \SplitIO\Sdk\Manager\SplitManagerInterface
     */
    public function manager(): \SplitIO\Sdk\Manager\SplitManagerInterface;
}
