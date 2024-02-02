<?php

namespace SplitIO;

use SplitIO\Component\Common\Context;

class Split
{
    /**
     * @return \Splitio\Component\Log\Logger
     */
    public static function logger()
    {
        return Context::getLogger();
    }
}
