<?php

namespace SplitIO\Engine;

use SplitIO\Engine\LegacyHash;
use SplitIO\Engine\Murmur3Hash;

class HashFactory
{
    public static function getHashAlgorithm($algo)
    {
        switch ($algo) {
            case HashAlgorithmEnum::MURMUR:
                return new MurMurHash();
            case HashAlgorithmEnum::LEGACY:
            default:
                return new LegacyHash();
        }
    }
}
