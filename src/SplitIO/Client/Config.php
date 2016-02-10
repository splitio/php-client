<?php
namespace SplitIO\Client;

class Config
{
    private $authorization = null;

    private $url = null;

    /**
     * @return null
     */
    public function getAuthorization()
    {
        return $this->authorization;
    }

    /**
     * @param null $authorization
     */
    public function setAuthorization($authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @return null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param null $url
     */
    public function setUrl($url)
    {
        $this->url = rtrim($url, '/');
    }
}
