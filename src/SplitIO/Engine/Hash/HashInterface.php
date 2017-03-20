<?php
namespace SplitIO\Engine;


interface HashInterface
{
    public function getHash($key, $seed);
}