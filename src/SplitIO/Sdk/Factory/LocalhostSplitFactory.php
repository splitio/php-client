<?php

namespace SplitIO\Sdk\Factory;

use SplitIO\Sdk\LocalhostClient;
use SplitIO\Sdk\Manager\LocalhostSplitManager;

class LocalhostSplitFactory implements SplitFactoryInterface
{
    private $client;

    private $manager;

    public function __construct(array $options = array())
    {

        $filePath = (isset($options['splitFile']) && file_exists($options['splitFile']))
            ? $options['splitFile']
            : null;

        $this->client = new LocalhostClient($filePath);

        $this->manager = new LocalhostSplitManager($this->client()->getSplits());
    }

    public function client()
    {
        return $this->client;
    }

    public function manager()
    {
        return $this->manager;
    }
}
