<?php

namespace SplitIO\Sdk\Factory;

interface SplitFactoryInterface
{
    /**
     * @return \SplitIO\Sdk\ClientInterface
     */
    public function client();

    /**
     * @return \SplitIO\Sdk\Manager\SplitManagerInterface
     */
    public function manager();
}
