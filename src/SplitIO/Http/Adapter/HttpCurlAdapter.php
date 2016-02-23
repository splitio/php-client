<?php
namespace SplitIO\Http\Adapter;

use SplitIO\Split;
use SplitIO\Http\Request;
use SplitIO\Http\ClientOptions;
use SplitIO\Http\Response;
use SplitIO\Http\MethodEnum;
use SplitIO\Http\Exception\HttpServerException;
use SplitIO\Http\Adapter\Exception\HttpCurlAdapterOtionsException;

class HttpCurlAdapter implements HttpAdapterInterface
{
    public $handle;

    /** @var array */
    public $httpOptions = array();

    /** @var \SplitIO\Http\Response */
    public $responseObject;

    /** @var array */
    public $responseInfo;

    /**
     * HttpCurlAdapter Constructor
     */
    public function __construct()
    {
        $this->httpOptions = array();
        $this->httpOptions[CURLOPT_RETURNTRANSFER] = true;
        $this->httpOptions[CURLOPT_FOLLOWLOCATION] = true;
        $this->httpOptions[CURLOPT_HEADER] = true;
        $this->httpOptions[CURLOPT_ENCODING] = 'gzip';
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return \SplitIO\Http\Response
     * @throws HttpServerException
     * @throws HttpServerException404
     * @throws RestClientException
     */
    public function doRequest(Request $request, $options = array())
    {
        $uri = $request->getUri();
        $this->handle = curl_init($uri);

        switch ($request->getMethod()) {
            case MethodEnum::GET:
                break;

            case MethodEnum::POST:
                Split::logger()->info("[HTTP cUrl adapter] POST method");

                $this->httpOptions[CURLOPT_POST] = true;

                $_data = $request->getData();

                $isJson = false;
                if ($request->getHeader("Content-Type") == "application/json") {
                    $isJson = true;
                    Split::logger()->info("[HTTP cUrl adapter] Content-Type: application/json");
                } elseif (is_array($_data)) {
                    $this->httpOptions[CURLOPT_HTTPHEADER][] = 'Content-Type: multipart/form-data';
                    Split::logger()->info("[HTTP cUrl adapter] Content-Type: multipart/form-data");
                }

                if (is_array($_data) && $isJson) {
                    Split::logger()->info("[HTTP cUrl adapter] Encoding Array to JSON");
                    $this->httpOptions[CURLOPT_POSTFIELDS] = json_encode($_data);
                } else {
                    $this->httpOptions[CURLOPT_POSTFIELDS] = $_data;
                }
                break;
            case MethodEnum::PUT:
                $this->httpOptions[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $this->httpOptions[CURLOPT_POSTFIELDS] = $request->getData();
                break;
            case MethodEnum::DELETE:
                $this->httpOptions[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
            default:
                break;
        }

        $this->setHeaders($request->getHeaders());
        $this->setOptions($options);
        $this->setOptRequest();

        $response_object = curl_exec($this->handle);
        $this->responseObject = $this->httpParseMessage($response_object);

        curl_close($this->handle);
        return $this->responseObject;
    }

    /**
     * @param array $headers
     */
    private function setHeaders(array $headers)
    {
        if (!empty($headers)) {
            if (!isset($this->httpOptions[CURLOPT_HTTPHEADER])) {
                $this->httpOptions[CURLOPT_HTTPHEADER] = array();
            }

            if (!is_array($this->httpOptions[CURLOPT_HTTPHEADER])) {
                $this->httpOptions[CURLOPT_HTTPHEADER] = array();
            }

            foreach ($headers as $k => $v) {
                $this->httpOptions[CURLOPT_HTTPHEADER][] = $k.': '.$v;
            }
        }
    }

    /**
     * @param array $options
     * @throws HttpCurlAdapterOtionsException
     */
    private function setOptions($options = array())
    {
        $this->httpOptions[CURLOPT_VERBOSE] = $options[ClientOptions::VERBOSE];
        $this->httpOptions[CURLOPT_TIMEOUT] = $options[ClientOptions::TIMEOUT];
        $this->httpOptions[CURLOPT_USERAGENT] = $options[ClientOptions::USERAGENT];
    }

    private function setOptRequest()
    {
        if (!curl_setopt_array($this->handle, $this->httpOptions)) {
            throw new HttpCurlAdapterOtionsException("Error setting cURL request options");
        }
    }

    /**
     * @param $res
     * @return Response
     * @throws HttpServerException
     */
    private function httpParseMessage($res)
    {
        if (!$res) {
            throw new HttpServerException(curl_error($this->handle), -1);
        }

        $this->responseInfo = curl_getinfo($this->handle);
        $code = $this->responseInfo['http_code'];

        $headers = $this->getHeadersFromCurlResponse($res, $this->responseInfo['header_size']);
        $body = substr($res, $this->responseInfo['header_size']);

        $response = new Response($code, $headers, $body);

        return $response;
    }

    /**
     * @param $response
     * @param $header_size
     * @return array
     */
    private function getHeadersFromCurlResponse($response, $header_size)
    {
        $headers = array();

        $headers_text = explode("\r\n\r\n", substr($response, 0, $header_size));

        $last_header_text = $headers_text[count($headers_text)-2];

        Split::logger()->info("HTTP Response Headers:");
        Split::logger()->info($last_header_text);

        foreach (explode("\r\n", $last_header_text) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }

        return $headers;
    }
}
