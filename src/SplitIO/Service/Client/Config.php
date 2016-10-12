<?php
namespace SplitIO\Service\Client;

class Config
{
    private $authorization = null;

    private $url = null;

    private $eventsUrl = null;

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

    /**
     * @return null
     */
    public function getEventsUrl()
    {
        return $this->eventsUrl;
    }

    /**
     * @param null $eventsUrl
     */
    public function setEventsUrl($eventsUrl)
    {
        $this->eventsUrl = rtrim($eventsUrl, '/');
    }
}
