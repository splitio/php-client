<?php
namespace SplitIO\Http;

class Request
{
    private $headers = array();

    private $data = null;

    private $method = null;

    private $uri=null;

    public function __construct(MethodEnum $method, $uri, $headers = array())
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
    }

    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return null|MethodEnum
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string|array $data
     */
    public function setData($data = '')
    {
        $this->data = $data;
    }

    /**
     * @return string|array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $key
     * @return null
     */
    public function getHeader($key)
    {
        return (isset($this->headers[$key])) ? $this->headers[$key] : null;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value)
    {
        $this->headers[(string) $key] = (string) $value;
    }
}
