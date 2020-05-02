<?php

namespace Paliari\NanoHttp;

use Exception;
use Paliari\NanoHttp\Http\Response;
use Paliari\NanoHttp\Http\Router;
use Paliari\Utils\Http\Request;

class App
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Router
     */
    protected $router;

    public function __construct()
    {
        if (!isset($GLOBALS['X-Time'])) {
            $GLOBALS['X-Time'] = microtime(true);
        }
        $this->response = new Response();
        $this->request  = new Request();
        $this->router   = new Router();
    }

    /**
     * Add middleware
     *
     * This method prepends new middleware to the application middleware stack.
     *
     * @param callable $callable Any callable that accepts three arguments:
     *                           1. A Request object
     *                           2. A Response object
     *                           3. A "next" middleware callable
     *
     * @param string   $route
     *
     * @return $this
     */
    public function add($callable, $route = '/')
    {
        $this->router->add($callable, $route);

        return $this;
    }

    /**
     * @param string   $method HTTP method: GET, POST, PUT, PATCH, DELETE, OPTIONS, HEADER
     * @param string   $route eg: /users/{id}
     * @param callable $callable
     *
     * @return $this
     */
    public function map($method, $route, $callable)
    {
        $this->router->map($method, $route, $callable);

        return $this;
    }

    /**
     * @param string   $route
     * @param callable $callable
     *
     * @return $this
     */
    public function get($route, $callable)
    {
        $this->map('GET', $route, $callable);

        return $this;
    }

    /**
     * @param string   $route
     * @param callable $callable
     *
     * @return $this
     */
    public function post($route, $callable)
    {
        $this->map('POST', $route, $callable);

        return $this;
    }

    /**
     * @param string   $route
     * @param callable $callable
     *
     * @return $this
     */
    public function put($route, $callable)
    {
        $this->map('PUT', $route, $callable);

        return $this;
    }

    /**
     * @param string   $route
     * @param callable $callable
     *
     * @return $this
     */
    public function patch($route, $callable)
    {
        $this->map('PATCH', $route, $callable);

        return $this;
    }

    /**
     * @param string   $route
     * @param callable $callable
     *
     * @return $this
     */
    public function delete($route, $callable)
    {
        $this->map('DELETE', $route, $callable);

        return $this;
    }

    public function run()
    {
        try {
            $this->response = $this->router->run($this->request, $this->response);
        } catch (Exception $e) {
            if (200 == $this->response->code) {
                $this->response->code = 409;
            }
            $this->response->body = $this->toJson(['error' => $e->getMessage()]);
        }
        echo $this->response;
    }

    protected function toJson($content)
    {
        return json_encode($content);
    }
}
