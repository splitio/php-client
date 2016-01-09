<?php
namespace SplitIO\Http\Adapter;

use Psr\Http\Message\RequestInterface;

interface HttpAdapterInterface
{
    public function doRequest(RequestInterface $request, $options = array());
}