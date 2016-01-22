<?php
namespace SplitIO;

use SplitIO\Http\Client as HttpClient;
use SplitIO\Http\MethodEnum;
use SplitIO\Http\Request;

class Client
{
    private $authorization = null;

    public function __construct($auth)
    {
        $this->authorization = $auth;
    }

    /**
     * @return bool|null
     */
    public function getSplitChanges()
    {

        $httpClient = new HttpClient();

        $request = new Request(MethodEnum::GET(), 'http://localhost:8081/api/splitChanges');
        $request->setHeader('Authorization', $this->authorization);
        $request->setHeader('SplitSDKVersion', 'php-0.0.1');

        $response = $httpClient->send($request);

        if ($response->isSuccess()) {
            return $response->getBody();
        }

        return false;
    }

    public function getSegmentChanges($segmentName, $since = -1)
    {
        $httpClient = new HttpClient();

        $request = new Request(MethodEnum::GET(), 'http://localhost:8081/api/segmentChanges/'.$segmentName.'?since='.$since);
        $request->setHeader('Authorization', $this->authorization);
        $request->setHeader('SplitSDKVersion', 'php-0.0.1');

        $response = $httpClient->send($request);

        if ($response->isSuccess()) {
            return $response->getBody();
        }

        return false;
    }


}