<?php
namespace SplitIO\Grammar;

use SplitIO\Grammar\Condition\Combiner\AndCombiner;
use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Grammar\Condition\Partition;

/*
{
  "matcherGroup": {
    "combiner": "AND",
    "matchers": [
      {
        "matcherType": "IN_SEGMENT",
        "negate": false,
        "userDefinedSegmentMatcherData": {
          "segmentName": "demo"
        },
        "whitelistMatcherData": null
      }
    ]
  },
  "partitions": [
    {
      "treatment": "on",
      "size": 10
    },
    {
      "treatment": "control",
      "size": 90
    }
  ]
}
*/
class Condition
{
    private $matcherGroup = null;

    private $partitions = null;

    private $combiner = null;

    public function __construct(array $condition)
    {
        $this->combiner = new AndCombiner();

        if (isset($condition['partitions'])) {
            $this->partitions = array();
            foreach ($condition['partitions'] as $partition) {
                $this->partitions[] = new Partition($partition);
            }
        }

        if (isset($condition['matcherGroup']['matchers'])) {
            $this->matcherGroup = [ 'matchers'=>[] ];

            foreach ($condition['matcherGroup']['matchers'] as $matcher) {
                $this->matcherGroup['matchers'] = Matcher::factory($matcher);
            }
        }

    }
}