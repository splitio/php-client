<?php
namespace SplitIO\Component\Common;

class ServiceProvider
{
    public static function registerLogger($logger)
    {
        Di::setLogger($logger);
    }
}
