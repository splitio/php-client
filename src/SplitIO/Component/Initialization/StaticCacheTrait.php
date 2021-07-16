<?php

namespace SplitIO\Component\Initialization;

use SplitIO\Component\Cache\StaticCache;
use SplitIO\Component\Common\ServiceProvider;

class StaticCacheTrait
{
    public static function addStaticCache()
    {
        ServiceProvider::registerStaticCache(new StaticCache());
    }
}
