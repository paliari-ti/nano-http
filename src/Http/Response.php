<?php

namespace Paliari\NanoHttp\Http;

class Response
{
    public $body = null;
    public $code = 200;

    protected $_headers = ['Content-Type' => 'application/json;charset=utf-8'];

    public function sendHeaders()
    {
        foreach ($this->_headers as $k => $v) {
            header("$k: $v");
        }
    }

    public function setHeader($key, $value)
    {
        $this->_headers[$key] = $value;
    }

    public function setHeaderHtml()
    {
        $this->setHeader('Content-Type', 'text/html');
    }

    public function redirect($url, $status_code = 302)
    {
        $this->_headers = [];
        $this->code     = $status_code;
        $this->setHeader('Location', $url);
    }

    public function send()
    {
        http_response_code($this->code);
        $this->setHeader('X-Time', $this->xTime() . 'ms');
        $this->sendHeaders();

        return $this->body;
    }

    public function __toString()
    {
        return (string)$this->send();
    }

    protected function xTime()
    {
        return round((microtime(true) - $GLOBALS['X-Time']) * 1000, 3);
    }
}
