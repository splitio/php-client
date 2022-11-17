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
}
