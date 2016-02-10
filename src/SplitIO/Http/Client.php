<?php
namespace SplitIO\Http;

use SplitIO\Http\Adapter\HttpCurlAdapter;

class Client implements ClientInterface
{
    /**
     * Http Adapter
     * @var null|\SplitIO\Http\Adapter\HttpAdapterInterface
     */
    protected $adapter = null;

    protected $options = [
        ClientOptions::TIMEOUT => 30,
        ClientOptions::ADAPTER => 'SplitIO\Http\Adapter\HttpCurlAdapter',
        ClientOptions::USERAGENT => 'SplitIO-PHP-SDK/1.0'
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        if (class_exists($this->options[ClientOptions::ADAPTER]) &&
            is_a($this->options[ClientOptions::ADAPTER], '\SplitIO\Http\Adapter\HttpAdapterInterface', true)) {
            $this->adapter = new $this->options[ClientOptions::ADAPTER];
        } else {
            $this->adapter = new HttpCurlAdapter();
        }
    }

    public function send(Request $request)
    {
        return $this->adapter->doRequest($request, $this->options);
    }
}
