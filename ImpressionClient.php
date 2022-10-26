<?php

namespace Listener;

// Implementation Sample for a Client Listener
class ImpressionClient implements \SplitIO\Sdk\ImpressionListener
{
    public function logImpression($data)
    {
        $impressionLogged = $data['impression'];
        $impression = array(
            'keyName' => $impressionLogged->getId(),
            'treatment' => $impressionLogged->getTreatment(),
            'time' => $impressionLogged->getTime(),
            'changeNumber' => $impressionLogged->getChangeNumber(),
            'label' => $impressionLogged->getLabel(),
            'bucketingKey' => $impressionLogged->getBucketingKey()
        );

        $customData = array(
            'impressions' => [
                array(
                    'testName' => $impressionLogged->getFeature(),
                    'keyImpressions' => [$impression]
                )
            ],
            'sdkVersion' => $data['sdk-language-version'],
            'machineIP' => $data['instance-id']
        );

        echo json_encode($customData) ."\n";

        //$this->post('http://localhost:9876/impressions', json_encode($customData));
    }

    /**
     * @param $uri
     * @param $body
     * @return Response
     */
    protected function post($uri, $body)
    {
        $response = \Requests::post($uri, array(), $body);

        echo json_encode($response->body) ."\n";
    }
}
