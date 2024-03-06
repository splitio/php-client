<?php

namespace SplitIO\Sdk\Factory;

use SplitIO\Sdk\Client;
use SplitIO\Sdk\Manager\SplitManager;
use SplitIO\Component\Cache\Pool;
use SplitIO\Component\Cache\EventsCache;
use SplitIO\Component\Cache\ImpressionCache;
use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\SplitCache;

/**
 * Class SplitFactory
 * @package SplitIO
 */
class SplitFactory implements SplitFactoryInterface
{
    /**
     * @var
     */
    private $options;

    /**
     * @var \SplitIO\Sdk\ClientInterface
     */
    private $client;

    /**
     * @var \SplitIO\SDK\Manager\SplitManagerInterface
     */
    private $manager;

    /**
     * @var \SplitIO\Component\Cache\Pool
     */
    private $cache;

    /**
     * @param string $sdkKey
     * @param Pool $cache
     * @param array $options
     */
    public function __construct(string $sdkKey, Pool $cache, array $options = array())
    {
        $this->options = $options;
        $this->cache = $cache;

        $eventCache = new EventsCache($cache);
        $impressionCache = new ImpressionCache($cache);
        $segmentCache = new SegmentCache($cache);
        $splitCache = new SplitCache($cache);

        $this->client = new Client(array(
            'splitCache' => $splitCache,
            'segmentCache' => $segmentCache,
            'impressionCache' => $impressionCache,
            'eventCache' => $eventCache,
        ), $options);

        $this->manager = new SplitManager($splitCache);
    }

    /**
     * @return \SplitIO\Sdk\ClientInterface
     */
    public function client(): \SplitIO\Sdk\ClientInterface
    {
        return $this->client;
    }

    /**
     * @return \SplitIO\Sdk\Manager\SplitManagerInterface
     */
    public function manager(): \SplitIO\Sdk\Manager\SplitManagerInterface
    {
        return $this->manager;
    }
}
