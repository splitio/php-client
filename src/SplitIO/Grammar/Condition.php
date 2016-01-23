<?php
namespace SplitIO\Grammar;

use SplitIO\Common\Di;
//use SplitIO\Grammar\Condition\Combiner\AndCombiner;
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

    //On the next versions the condition will support Combiners: AND, OR, NOT
    //private $combiner = null;

    public function __construct(array $condition)
    {
        Di::getInstance()->getLogger()->debug(print_r($condition, true));
        Di::getInstance()->getLogger()->info("Constructing Condition");

        //On the next versions the condition will support Combiners: AND, OR, NOT
        //$this->combiner = new AndCombiner();

        if (isset($condition['partitions']) && is_array($condition['partitions'])) {
            $this->partitions = array();
            foreach ($condition['partitions'] as $partition) {
                $this->partitions[] = new Partition($partition);
            }
        }

        if (isset($condition['matcherGroup']['matchers']) && is_array($condition['matcherGroup']['matchers'])) {

            $this->matcherGroup = [];

            foreach ($condition['matcherGroup']['matchers'] as $matcher) {
                $this->matcherGroup[] = Matcher::factory($matcher);
            }
        }
    }

    public function match($userId)
    {
        $eval = [];
        foreach ($this->matcherGroup as $matcher) {

            if ($matcher instanceof \SplitIO\Grammar\Condition\Matcher\AbstractMatcher) {
                $eval[] = $matcher->evaluate($userId);
            }
        }

        //On the next versions the condition will support Combiners: AND, OR, NOT
        return (isset($eval[0])) ? $eval[0] : false;
    }

    public function getPartitions()
    {
        return $this->partitions;
    }

    public function getInvolvedUsers()
    {
        $users = [];
        foreach ($this->matcherGroup as $matcher) {

            if ($matcher instanceof \SplitIO\Grammar\Condition\Matcher\AbstractMatcher) {
                $users = array_merge($users, $matcher->getUsers());
            }
        }

        return $users;
    }
}