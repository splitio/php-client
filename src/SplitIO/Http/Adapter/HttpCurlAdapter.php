<?php
namespace SplitIO\Http\Adapter;

use Psr\Http\Message\RequestInterface;

class HttpCurlAdapter implements HttpAdapterInterface
{
    public $handle;
    public $http_options;
    public $response_object;
    public $response_info;

    function __construct() {
        $this->http_options = array();
        $this->http_options[CURLOPT_RETURNTRANSFER] = true;
        $this->http_options[CURLOPT_FOLLOWLOCATION] = true;
    }


    public function doRequest(RequestInterface $request, $options = array())
    {
        $uri = $request->getUri()->__toString();
        $this->handle = curl_init($uri);

        $http_options = $options + $this->http_options;

        if(! curl_setopt_array($this->handle, $http_options)){
            throw new RestClientException("Error setting cURL request options");
        }

        $this->response_object = curl_exec($this->handle);
        $this->http_parse_message($this->response_object);

        curl_close($this->handle);
        return $this->response_object;

    }

    /**
     * Perform a GET call to server
     *
     * Additionaly in $response_object and $response_info are the
     * response from server and the response info as it is returned
     * by curl_exec() and curl_getinfo() respectively.
     *
     * @param string $url The url to make the call to.
     * @param array $http_options Extra option to pass to curl handle.
     * @return string The response from curl if any
     */
    function get($url, $http_options = array()) {
        $http_options = $http_options + $this->http_options;
        $this->handle = curl_init($url);

        if(! curl_setopt_array($this->handle, $http_options)){
            throw new RestClientException("Error setting cURL request options");
        }

        $this->response_object = curl_exec($this->handle);
        $this->http_parse_message($this->response_object);

        curl_close($this->handle);
        return $this->response_object;
    }

    /**
     * Perform a POST call to the server
     *
     * Additionaly in $response_object and $response_info are the
     * response from server and the response info as it is returned
     * by curl_exec() and curl_getinfo() respectively.
     *
     * @param string $url The url to make the call to.
     * @param string|array The data to post. Pass an array to make a http form post.
     * @param array $http_options Extra option to pass to curl handle.
     * @return string The response from curl if any
     */
    function post($url, $fields = array(), $http_options = array()) {
        $http_options = $http_options + $this->http_options;
        $http_options[CURLOPT_POST] = true;
        $http_options[CURLOPT_POSTFIELDS] = $fields;
        if(is_array($fields)){
            $http_options[CURLOPT_HTTPHEADER] =
                array('Content-Type: multipart/form-data');
        }
        $this->handle = curl_init($url);

        if(! curl_setopt_array($this->handle, $http_options)){
            throw new RestClientException("Error setting cURL request options.");
        }

        $this->response_object = curl_exec($this->handle);
        $this->http_parse_message($this->response_object);

        curl_close($this->handle);
        return $this->response_object;
    }

    /**
     * Perform a PUT call to the server
     *
     * Additionaly in $response_object and $response_info are the
     * response from server and the response info as it is returned
     * by curl_exec() and curl_getinfo() respectively.
     *
     * @param string $url The url to make the call to.
     * @param string|array The data to post.
     * @param array $http_options Extra option to pass to curl handle.
     * @return string The response from curl if any
     */
    function put($url, $data = '', $http_options = array()) {
        $http_options = $http_options + $this->http_options;
        $http_options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        $http_options[CURLOPT_POSTFIELDS] = $data;
        $this->handle = curl_init($url);

        if(! curl_setopt_array($this->handle, $http_options)){
            throw new RestClientException("Error setting cURL request options.");
        }

        $this->response_object = curl_exec($this->handle);
        $this->http_parse_message($this->response_object);

        curl_close($this->handle);
        return $this->response_object;
    }

    /**
     * Perform a DELETE call to server
     *
     * Additionaly in $response_object and $response_info are the
     * response from server and the response info as it is returned
     * by curl_exec() and curl_getinfo() respectively.
     *
     * @param string $url The url to make the call to.
     * @param array $http_options Extra option to pass to curl handle.
     * @return string The response from curl if any
     */
    function delete($url, $http_options = array()) {
        $http_options = $http_options + $this->http_options;
        $http_options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        $this->handle = curl_init($url);

        if(! curl_setopt_array($this->handle, $http_options)){
            throw new RestClientException("Error setting cURL request options.");
        }

        $this->response_object = curl_exec($this->handle);
        $this->http_parse_message($this->response_object);

        curl_close($this->handle);
        return $this->response_object;
    }

    private function http_parse_message($res) {

        if(! $res){
            throw new HttpServerException(curl_error($this->handle), -1);
        }

        $this->response_info = curl_getinfo($this->handle);
        $code = $this->response_info['http_code'];

        if($code == 404) {
            throw new HttpServerException404(curl_error($this->handle));
        }

        if($code >= 400 && $code <=600) {
            throw new HttpServerException('Server response status was: ' . $code .' with response: [' . $res . ']', $code);
        }

        if(!in_array($code, range(200,207))) {
            throw new HttpServerException('Server response status was: ' . $code .
                ' with response: [' . $res . ']', $code);
        }
    }
}

class HttpServerException extends \Exception {
}

class HttpServerException404 extends \Exception {
    function __construct($message = 'Not Found') {
        parent::__construct($message, 404);
    }
}

class RestClientException extends \Exception {
}