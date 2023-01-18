<?php

namespace SplitIO\Engine\Hash;

class HashFactory
{
    /**
     * @param $algo
     * @return \SplitIO\Engine\Hash\HashInterface
     */
    public static function getHashAlgorithm($algo)
    {
        switch ($algo) {
            case HashAlgorithmEnum::MURMUR:
                return new Murmur3Hash();
            case HashAlgorithmEnum::LEGACY:
            default:
                return new LegacyHash();
        }
    }
}
