<?php
namespace SplitIO\Http\Adapter;

use SplitIO\Http\Request;

interface HttpAdapterInterface
{
    /**
     * @param Request $request
     * @param array $options
     * @return \SplitIO\Http\Response
     */
    public function doRequest(Request $request, $options = array());
}