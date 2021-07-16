<?php

namespace SplitIO\Component\Initialization;

use SplitIO\Component\Cache\StaticCache;
use SplitIO\Component\Common\ServiceProvider;

class StaticCacheTrait
{
    public static function addStaticCache($options = [])
    {
        $class = $options['class'] ?? StaticCache::class;
        ServiceProvider::registerStaticCache(new $class($options ?? []));
    }
}
