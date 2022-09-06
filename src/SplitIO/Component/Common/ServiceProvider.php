<?php
namespace SplitIO\Component\Common;

class ServiceProvider
{
    public static function registerLogger($logger)
    {
        Di::setLogger($logger);
    }

    public static function registerCache(\SplitIO\Component\Cache\Pool $cache)
    {
        Di::setCache($cache);
    }
}
