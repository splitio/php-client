<?php
namespace SplitIO\Engine\Hash;

interface HashInterface
{
    public function getHash($key, $seed);
}
