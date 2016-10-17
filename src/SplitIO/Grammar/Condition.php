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
            $this->matcherGroup = array();

            foreach ($condition['matcherGroup']['matchers'] as $matcher) {
                $this->matcherGroup[] = Matcher::factory($matcher);
            }
        }
    }

    /**
     * @param $key
     * @param array|null $attributes
     * @return bool
     */
    public function match($key, array $attributes = null)
    {
        $eval = array();
        foreach ($this->matcherGroup as $matcher) {
            if ($matcher instanceof AbstractMatcher) {
                $_evaluation = false;
                //Check if Matcher has attributes
                if (!$matcher->hasAttribute()) {
                    // scenario 1: no attr in matcher
                    // e.g. if user is in segment all then split 100:on
                    $_evaluation = $matcher->evaluate($key);
                } else {
                    // scenario 2: attribute provided but no attribute value provided. Matcher does not match
                    // e.g. if user.age is >= 10 then split 100:on
                    if ($attributes === null || !isset($attributes[$matcher->getAttribute()])) {
                        $_evaluation = false;
                    } else {
                        // instead of using the user id, we use the attribute value for evaluation
                        $attrValue = $attributes[$matcher->getAttribute()];
                        $_evaluation = $matcher->evaluate($attrValue);
                    }
                }

                //If matcher is Negate or not
                $eval[] = ($matcher->isNegate()) ? NotFactor::evaluate($_evaluation) : $_evaluation ;
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

    /**
     * @return array
     */
    public function getTreatments()
    {
        $treatments = array();
        if ($this->partitions) {
            foreach ($this->partitions as $partition) {
                $treatments[] = $partition->getTreatment();
            }
        }

        return $treatments;
    }
}
