<?php
namespace SplitIO\Client;

use SplitIO\Common\Di;
use SplitIO\Http\MethodEnum;
use SplitIO\Http\Request;

class ClientBase
{
    /**
     * @var null|Config
     */
    protected $config = null;

    public function __construct()
    {
        $this->config = Di::getInstance()->getSplitClientConfiguration();
    }

    protected function getRequest(MethodEnum $method, $servicePath)
    {
        $request = new Request($method, $this->config->getUrl(). $servicePath);

        return $this->setCommonHeaders($request);
    }

    protected function setCommonHeaders(Request $request)
    {
        $authorization =  $this->config->getAuthorization();

        $request->setHeader('Authorization', $authorization);
        $request->setHeader('SplitSDKVersion', 'php-0.0.1');
        $request->setHeader('User-Agent', 'SplitIO-SDK-PHP/0.1');

        return $request;
    }
}
