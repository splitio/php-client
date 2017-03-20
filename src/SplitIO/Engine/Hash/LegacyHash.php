<?php

namespace SplitIO\Engine\Hash;

class LegacyHash implements HashInterface
{
    public function getHash($key, $seed)
    {
        $h = 0;
        for ($i = 0; $i < strlen($key); $i++) {
            $h = \SplitIO\toInt32(\SplitIO\toInt32(31 * $h) + ord($key[$i]));
        }
        return \SplitIO\toInt32($h ^ $seed);
    }
}
