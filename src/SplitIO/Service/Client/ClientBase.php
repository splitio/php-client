<?php
namespace SplitIO\Service\Client;

//use GuzzleHttp\Client;
use SplitIO\Component\Common\Di;
use SplitIO\Component\Http\MethodEnum;
//use GuzzleHttp\Psr7\Request;
use SplitIO\Service\Client\Resource\ResourceTypeEnum;

abstract class ClientBase
{
    /**
     * @var null|Config
     */
    protected $config = null;

    protected $httpTransport = null;

    protected $resourceType = null;

    public function __construct()
    {
        $this->config = Di::get(Di::KEY_SPLIT_CLIENT_CONFIG);

        $this->httpTransport = array('transport' => 'Requests_Transport_fsockopen');

        $this->resourceType = $this->getResourceType();
    }

    private function getBaseUrl()
    {
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
    abstract public function getResourceType();

    /**
     * @param $servicePath
     * @return Response
     */
    protected function get($servicePath)
    {
        $uri = $this->getBaseUrl() . $servicePath;

        $requestResponse = \Requests::get($uri, $this->getCommonHeaders(), $this->httpTransport);

        return $this->decorateResponse($requestResponse);
    }

    /**
     * @param $servicePath
     * @param $body
     * @return Response
     */
    protected function post($servicePath, $body)
    {
        $uri = $this->getBaseUrl() . $servicePath;

        $requestResponse = \Requests::post($uri, $this->getCommonHeaders(), json_encode($body), $this->httpTransport);

        return $this->decorateResponse($requestResponse);
    }

    /**
     * @param \Requests_Response $requestResponse
     * @return Response
     */
    private function decorateResponse(\Requests_Response $requestResponse)
    {
        return new Response($requestResponse);
    }

    /**
     * @return array
     */
    private function getCommonHeaders()
    {
        $authorization =  $this->config->getAuthorization();

        $headers = array(
            'Authorization'     => 'Bearer ' . $authorization,
            'SplitSDKVersion'   => 'php-' . \SplitIO\version(),
            'User-Agent'        => 'SplitIO-SDK-PHP/0.1',
            'Accept-Encoding'   => 'gzip',
            'Content-Type'      => 'application/json'

        );

        return $headers;
    }
}
