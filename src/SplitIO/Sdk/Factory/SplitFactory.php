<?php
namespace SplitIO\Sdk\Factory;

use SplitIO\Component\Stats\Latency;
use SplitIO\Exception\TimeOutException;
use SplitIO\Sdk\Client;
use SplitIO\Sdk\LocalhostClient;
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
     * @param string $apiKey
     * @param array $options
     */
    public function __construct($apiKey, Pool $cache, array $options = array())
    {
        $this->options = $options;
        $this->cache = $cache;

        //Block until ready
        $this->doBUR();

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

    private function doBUR()
    {
        /*
            Deprecated
            $ready =  (isset($this->options['ready']) && $this->options['ready'] > 0) ? $this->options['ready'] : null;

            //Block Until Ready
            if ($ready) {
                if (!$this->blockUntilReady($ready)) {
                    throw new TimeOutException("Cache data is not ready yet");
                }
            }
        */
    }

    /**
     * @return \SplitIO\Sdk\ClientInterface
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * @return \SplitIO\Sdk\Manager\SplitManager
     */
    public function manager()
    {
        return $this->manager;
    }
}
