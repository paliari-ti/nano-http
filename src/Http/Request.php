<?php

namespace Paliari\NanoHttp\Http;

use Paliari\Utils\A;
use Paliari\Utils\AbstractSingleton;

class Request extends AbstractSingleton
{
    protected $_payload;

    protected $_headers = [];

    public function getPath()
    {
        return explode('?', $this->getRequestUri())[0];
    }

    public function getRequestUri()
    {
        return A::get($_SERVER, 'REQUEST_URI', '/');
    }

    public function getMethod()
    {
        return A::get($_SERVER, 'REQUEST_METHOD', 'GET');
    }

    public function getBody($key = null)
    {
        if (null === $this->_payload) {
            $this->_payload = json_decode(file_get_contents('php://input'), true) ?: [];
        }
        if ($key) {
            return A::deepKey($this->_payload, $key);
        }

        return $this->_payload;
    }

    public function post($key = null)
    {
        if ($key) {
            return A::deepKey($_POST, $key);
        }

        return $_POST;
    }

    public function get($key = null)
    {
        if ($key) {
            return A::deepKey($_GET, $key);
        }

        return $_GET;
    }

    public function userAgent()
    {
        return $this->getHeader('User-Agent');
    }

    public function ip()
    {
        return A::get($_SERVER, 'REMOTE_ADDR', '');
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getHeader($key)
    {
        return A::get($this->getHeaders(), $this->prepareHeaderKey($key));
    }

    protected function prepareHeaderKey($key)
    {
        return str_replace(' ', '-', ucwords(strtr(strtolower($key), '_-', '  ')));
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        if (!$this->_headers) {
            foreach ($this->httpHeaders() as $k => $v) {
                $this->_headers[$this->prepareHeaderKey($k)] = $v;
            }
        }

        return $this->_headers;
    }

    protected function httpHeaders()
    {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        if (!$headers) {
            $prefix = 'HTTP_';
            foreach ($_SERVER as $k => $v) {
                if (0 === strpos($k, $prefix)) {
                    $k                                    = str_replace($prefix, '', $k);
                    $headers[$this->prepareHeaderKey($k)] = $v;
                }
            }
            foreach (['CONTENT_TYPE', 'CONTENT_LENGTH', 'Authorization'] as $key) {
                if ($value = A::get($_SERVER, $key)) {
                    $headers[$this->prepareHeaderKey($key)] = $value;
                }
            }
        }

        return $headers;
    }
}
