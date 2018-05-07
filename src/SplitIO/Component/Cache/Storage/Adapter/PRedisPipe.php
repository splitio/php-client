<?php
namespace SplitIO\Component\Cache\Storage\Adapter;

class PRedisPipe
{
    public function __construct($pipe)
    {
        $this->_pipe = $pipe;
    }

    public function saveItemOnList($set, $item)
    {
        $this->_pipe->sAdd($set, $item);
    }
}
