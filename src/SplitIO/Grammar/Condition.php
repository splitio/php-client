<?php
namespace SplitIO\Grammar;

use SplitIO\Split as SplitApp;
use SplitIO\Grammar\Condition\Combiner\AndCombiner;
use SplitIO\Grammar\Condition\Combiner\CombinerEnum;
use SplitIO\Grammar\Condition\Combiner\Factor\NotFactor;
use SplitIO\Grammar\Condition\Matcher;
use SplitIO\Grammar\Condition\Partition;
use SplitIO\Grammar\Condition\Matcher\AbstractMatcher;

class Condition
{
    private $matcherGroup = null;

    private $partitions = null;

    //On the next versions the condition will support Combiners: AND, OR, NOT
    private $combiner = null;

    /**
     * @param array $condition
     */
    public function __construct(array $condition)
    {
        SplitApp::logger()->debug(print_r($condition, true));
        SplitApp::logger()->info("Constructing Condition");

        //So far the combiner is AND. On next versions the condition will support Combiners: OR
        $this->combiner = new CombinerEnum(CombinerEnum::_AND);

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

    /**
     * @param $key
     * @return bool
     */
    public function match($key)
    {
        $eval = [];
        foreach ($this->matcherGroup as $matcher) {
            if ($matcher instanceof AbstractMatcher) {
                $eval[] = ($matcher->isNegate())
                    ? NotFactor::evaluate($matcher->evaluate($key))
                    : $matcher->evaluate($key);
            }
        }

        if ($this->combiner instanceof CombinerEnum) {
            switch ($this->combiner->getValue()) {
                case CombinerEnum::_AND:
                default:
                    return AndCombiner::evaluate($eval);
            }
        }

        return false;
    }

    /**
     * @return array|null
     */
    public function getPartitions()
    {
        return $this->partitions;
    }
}
