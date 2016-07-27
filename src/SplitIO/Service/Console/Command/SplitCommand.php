<?php
namespace SplitIO\Service\Console\Command;

use SplitIO\Component\Cache\SegmentCache;
use SplitIO\Component\Cache\SplitCache;

class SplitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('fetch-splits')
            ->setDescription('Fetch Splits definitons from server');
    }

    public function execute()
    {
        //Fetching the Splits changes
        $splitChanges = $this->getSplitClient()->getSplitChanges();
        $splits = (isset($splitChanges['splits'])) ? $splitChanges['splits'] : array();

        $splitCache = new SplitCache();

        if (!empty($splits)) {
            foreach ($splits as $split) {
                if (!is_array($split)
                    || !isset($split['name'])
                    || !isset($split['conditions'])
                    || !isset($split['status'])) {
                    continue; //continue with next Split
                }

                $this->logger()->debug(print_r($split, true));

                $splitName = $split['name'];
                $splitStatus = $split['status'];

                $splitConditions = $split['conditions'];

                foreach ($splitConditions as $condition) {
                    foreach ($condition['matcherGroup']['matchers'] as $matcher) {
                        if ($matcher['matcherType'] == "IN_SEGMENT") {
                            //Register segment to retrieve Segment Data.
                            SegmentCache::registerSegment($matcher['userDefinedSegmentMatcherData']['segmentName']);
                        }
                    }
                }

                if ($splitStatus == 'ACTIVE') { //Update Cache

                    $splitCache->addSplit($splitName, json_encode($split));
                } else { //Delete item from cache

                    $splitCache->removeSplit($splitName);
                }
            }
        }

        if (isset($splitChanges['till'])) {
            $till = $splitChanges['till'];
            $this->logger()->debug("Splits Till value: $till");

            //Updating next since (till) value.
            if ($till != $splitCache->getChangeNumber()) {
                $splitCache->setChangeNumber($till);
            }
        }
    }
}
