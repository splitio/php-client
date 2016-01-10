<?php
namespace SplitIO\Http\Adapter;

use SplitIO\Http\Request;

interface HttpAdapterInterface
{
    public function doRequest(Request $request, $options = array());
}