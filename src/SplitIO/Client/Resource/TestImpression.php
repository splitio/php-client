<?php
namespace SplitIO\Client\Resource;

use SplitIO\Client\ClientBase;
use SplitIO\Http\Client as HttpClient;
use SplitIO\Http\MethodEnum;
use SplitIO\Split as SplitApp;

class TestImpression extends ClientBase
{
    private $servicePath = '/api/testImpressions';

    public function sendTestImpressions($featureName, $impressions)
    {
        $httpClient = new HttpClient();

        $data = ['testName' => $featureName, 'keyImpressions' => $impressions];

        $request = $this->getRequest(MethodEnum::POST(), $this->servicePath);
        $request->setData($data);
        $request->setHeader("Content-Type", "application/json");

        $response = $httpClient->send($request);

        if ($response->isSuccess()) {
            $responseData = json_decode($response->getBody(), true);
            SplitApp::logger()->info("Impressions sent successfuly - response code ".$responseData);
            return true;
        }

        return false;
    }

}