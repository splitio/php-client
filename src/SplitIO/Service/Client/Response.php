<?php
namespace SplitIO\Service\Client;

class Response
{

    private $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * @return \Requests_Response
     */
    private function getInternalResponse()
    {
        return $this->response;
    }

    public function getStatusCode()
    {
        return $this->getInternalResponse()->status_code;
    }

    public function getBody()
    {
        return $this->getInternalResponse()->body;
    }

    public function isSuccessful()
    {
        $statusCode = $this->getStatusCode();

        if (false === is_int($statusCode)) {
            trigger_error('ResponseHelper::isSuccessful expected Argument 1 to be Integer', E_USER_WARNING);
        }

        return $statusCode >= 200 && $statusCode < 300;
    }
}
