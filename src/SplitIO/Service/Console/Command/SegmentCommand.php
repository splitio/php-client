<?php
namespace SplitIO\Service\Console\Command;

use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Stats\Latency;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SegmentCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('fetch-segments')
            ->setDescription('Fetch Segment keys from server');
    }

    public function execute()
    {
        $registeredSegments = $this->cache()->getItemsOnList(SegmentCache::getCacheKeyForRegisterSegments());

        $log = $this->logger();

        if (is_array($registeredSegments) && !empty($registeredSegments)) {
            foreach ($registeredSegments as $segmentName) {
                $log->info(">>> Fetching data from segment: $segmentName");
                $timeStart = Latency::startMeasuringLatency();
                while (true) {
                    $timeStartPart = Latency::startMeasuringLatency();
                    if (! $this->getSplitClient()->updateSegmentChanges($segmentName)) {
                        $timeItTook = Latency::calculateLatency($timeStartPart);
                        $log->debug("Fetching segment last part ($segmentName) took $timeItTook microseconds");
                        $greedyTime = Latency::calculateLatency($timeStart);
                        $log->info("Finished fetching whole segment $segmentName, took $greedyTime microseconds");
                        break;
                    }

                    $timeItTook = Latency::calculateLatency($timeStartPart);
                    $log->debug("Fetching segment part ($segmentName) took $timeItTook microseconds");

                    //Sleep 1/2 second
                    usleep(500000);
                }
            }
        }
    }
}
