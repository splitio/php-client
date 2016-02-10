<?php
namespace SplitIO\Grammar\Condition\Combiner;

interface CombinerInterface
{
    public static function evaluate(array $terms);
}
