<?php
namespace SplitIO\Http;

use SplitIO\Http\Adapter\HttpSocketAdapter;
use SplitIO\Http\Adapter\HttpCurlAdapter;
use Psr\Http\Message\RequestInterface;

class Client
{
    /**
     * Http Adapter
     * @var null|HttpSocketAdapter
     */
    protected $adapter=null;

    public function __construct()
    {
        //$this->adapter = new HttpSocketAdapter();
        $this->adapter = new HttpCurlAdapter();
    }

    public function send(RequestInterface $request)
    {
        //$this->adapter->doRequest($request,$this->timeout);
        return $this->adapter->doRequest($request);
    }



}