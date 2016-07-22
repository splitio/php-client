<?php
namespace SplitIO\Service\Client;

use GuzzleHttp\Client;
use SplitIO\Component\Common\Di;
use SplitIO\Component\Http\MethodEnum;
use GuzzleHttp\Psr7\Request;
use SplitIO\Service\Client\Resource\ResourceTypeEnum;

abstract class ClientBase
{
    /**
     * @var null|Config
     */
    protected $config = null;

    protected $httpClient = null;

    protected $resourceType = null;

    public function __construct()
    {
        $this->config = Di::get(Di::KEY_SPLIT_CLIENT_CONFIG);

        $this->httpClient = new Client();

        $this->resourceType = $this->getResourceType();
    }

    private function getBaseUrl(){
        switch ($this->resourceType->getValue()) {
            case ResourceTypeEnum::EVENT:
                return $this->config->getEventsUrl();
                break;

            case ResourceTypeEnum::SDK:
            default:
                return $this->config->getUrl();
                break;
        }

    }

    /**
     * @return \SplitIO\Service\Client\Resource\ResourceTypeEnum $type
     */
    public abstract function getResourceType();

    /**
     * @param $servicePath
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function get($servicePath)
    {
        $data = array(
            'headers' => $this->getCommonHeaders()
        );

        $uri = $this->getBaseUrl() . $servicePath;

        return $this->httpClient->request('GET',$uri, $data);
    }

    /**
     * @param $servicePath
     * @param $body
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    protected function post($servicePath, $body)
    {

        $data = array(
            'headers' => $this->getCommonHeaders(),
            'json' => $body
        );

        $uri = $this->getBaseUrl() . $servicePath;

        return $this->httpClient->request('POST',$uri, $data);
    }

    /**
     * @return array
     */
    private function getCommonHeaders()
    {
        $authorization =  $this->config->getAuthorization();

        $headers = array(
            'Authorization'     => 'Bearer ' . $authorization,
            'SplitSDKVersion'   => 'php-0.0.1',
            'User-Agent'        => 'SplitIO-SDK-PHP/0.1',
            'Accept-Encoding'   => 'gzip',
            'Content-Type'      => 'application/json'

        );

        return $headers;
    }
}
