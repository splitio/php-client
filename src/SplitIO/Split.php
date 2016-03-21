<?php
namespace SplitIO;

use SplitIO\Component\Common\Di;

class Split
{

    /**
     * @return null|\Psr\Log\LoggerInterface
     */
    public static function logger()
    {
        return Di::getLogger();
    }

    /**
     * @return null|\SplitIO\Component\Cache\Pool
     */
    public static function cache()
    {
        return Di::getCache();
    }
}
