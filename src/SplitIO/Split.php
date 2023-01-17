<?php
namespace SplitIO;

use SplitIO\Component\Common\Context;

class Split
{

    /**
     * @return null|\Psr\Log\LoggerInterface
     */
    public static function logger()
    {
        return Context::getLogger();
    }
}
