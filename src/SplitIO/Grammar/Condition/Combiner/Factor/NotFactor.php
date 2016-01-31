<?php
namespace SplitIO\Grammar\Condition\Combiner\Factor;

class NotFactor implements FactorInterface
{
    public static function evaluate($factor)
    {
        if (is_bool($factor)) {
            return ! $factor;
        }

        return false;
    }
}