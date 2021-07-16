<?php
namespace SplitIO\Component\Common;

class ServiceProvider
{
    public static function registerLogger(\Psr\Log\LoggerInterface $logger)
    {
        Di::setLogger($logger);
    }

    public static function registerCache(\SplitIO\Component\Cache\Pool $cache)
    {
        Di::setCache($cache);
    }

    public static function registerStaticCache(\SplitIO\Component\Cache\StaticCache $staticCache)
    {
        Di::setStaticCache($staticCache);
    }
}
