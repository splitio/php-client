<?php
namespace SplitIO\Http;

class Response
{
    private $statusCode = 200;

    private $headers = array();

    private $body = null;

    public function __construct($code, array $headers = [], $body = null)
    {
        $this->statusCode = $code;

        $this->headers = $headers;

        $this->body = $body;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function isSuccess()
    {
        return ($this->statusCode >= 200 && $this->statusCode <= 207)? true : false;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($key)
    {
        return (isset($this->headers[$key])) ? $this->headers[$key] : null;
    }

    public function getBody()
    {
        return $this->body;
    }
}
