<?php
namespace SplitIO\Sdk\Factory;

use SplitIO\Component\Cache\BlockUntilReadyCache;
use SplitIO\Component\Stats\Latency;
use SplitIO\Exception\TimeOutException;
use SplitIO\Sdk\Client;
use SplitIO\Sdk\LocalhostClient;
use SplitIO\Sdk\Manager\SplitManager;

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
     * @var SplitManager
     */
    private $manager;

    /**
     * @param string $apiKey
     * @param array $options
     */
    public function __construct($apiKey, array $options = array())
    {
        $this->options = $options;

        //Block until ready
        $this->doBUR();

        $this->client = new Client($options);

        $this->manager = new SplitManager();
    }

    private function doBUR()
    {
        $ready =  (isset($this->options['ready']) && $this->options['ready'] > 0) ? $this->options['ready'] : null;

        //Block Until Ready
        if ($ready) {
            if (!$this->blockUntilReady($ready)) {
                throw new TimeOutException("Cache data is not ready yet");
            }
        }
    }

    /**
     * @param $timeout
     * @return bool
     */
    private function blockUntilReady($timeout)
    {
        $bur = new BlockUntilReadyCache();

        $startTime = Latency::startMeasuringLatency();

        do {
            $lastreadyCheckpoint = $bur->getReadyCheckpoint();

            if ($lastreadyCheckpoint > 0) {
                return true;
            }

            // Checkpoint in milliseconds
            $checkPoint = Latency::calculateLatency($startTime) / 1000;

            // waiting 10 milliseconds
            usleep(10000);
        } while ($checkPoint < $timeout);

        return false;
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
