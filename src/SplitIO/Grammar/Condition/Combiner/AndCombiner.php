<?php
namespace SplitIO\Grammar\Condition\Combiner;

class AndCombiner implements CombinerInterface
{
    /**
     * @param array $factors
     * @return bool
     */
    public static function evaluate(array $factors)
    {
        $return = true;
        if (!empty($factors)) {
            foreach ($factors as $factor) {
                if (is_bool($factor)) {
                    $return = $return && $factor;
                }
            }
        }

        return $return;
    }
}
