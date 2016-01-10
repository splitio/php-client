<?php
/**
 * Created by PhpStorm.
 * User: sarrubia
 * Date: 10/01/16
 * Time: 14:24
 */

namespace SplitIO\Http;

class Request
{
    private $headers = array();

    private $data = null;

    private $method = null;

    private $uri=null;

    public function __construct(Method $method, $uri)
    {
        $this->method = $method;
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return null|Method
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
}