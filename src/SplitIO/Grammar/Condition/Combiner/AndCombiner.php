<?php
namespace SplitIO\Grammar\Condition\Combiner;

class AndCombiner implements CombinerInterface
{

    public static function evaluate(array $factors)
    {
        $return = true;
        if (!empty($factors)) {
            foreach ($factors as $factor) {
                if (is_bool($factor)) {
                    $return = $return && $factor;
                } else {
                    return false;
                }
            }
        }

        return $return;
    }
}
