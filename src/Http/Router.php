<?php

namespace Paliari\NanoHttp\Http;

use DomainException;
use Paliari\Utils\A;
use Paliari\Utils\Http\Request;

class Router
{
    protected $routes      = [];
    protected $_middleware = [];

    /**
     * @param string   $method HTTP method: GET, POST, PUT, PATCH, DELETE, OPTIONS, HEADER
     * @param string   $route eg: /users/{id}
     * @param callable $callable
     *
     * @return $this
     */
    public function map($method, $route, $callable)
    {
        $this->routes[strtoupper($method)][$this->routeToPattern($route)] = $callable;

        return $this;
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
        $pattern                     = $this->routeToPattern($route, '');
        $this->_middleware[$pattern] = $callable;

        return $this;
    }

    public function run(Request $request, Response $response)
    {
        $run = $this->getRunWithMiddleware($request->getPath());

        return $run($request, $response);
    }

    protected function runRoute(Request $request, Response $response)
    {
        $patterns = A::get($this->routes, $request->getMethod(), []);
        foreach ($patterns as $pattern => $callable) {
            if (preg_match($pattern, $request->getPath(), $p)) {
                return $this->invoke($callable, array_slice($p, 1), $request, $response);
            }
        }
        $response->code = 404;
        throw new DomainException('Page not found!');
    }

    protected function invoke($callable, $route_args, $request, $response)
    {
        if (is_string($callable) && preg_match('/\w+\:\w+/', $callable)) {
            list($class, $method) = explode(':', $callable);
            $callable = [new $class(), $method];
        }
        $args = array_merge([$request, $response], $route_args);

        return call_user_func_array($callable, $args);
    }

    /**
     * @param string $path
     *
     * @return callable
     */
    protected function getRunWithMiddleware($path)
    {
        $next = $this;
        foreach ($this->getMiddlewareOfRun($path) as $callable) {
            $next = $this->createMiddlewareNextRun($callable, $next);
        }

        return $next;
    }

    /**
     * @param callable $callable
     * @param callable $next
     *
     * @return callable
     */
    protected function createMiddlewareNextRun($callable, $next)
    {
        return function (Request $request, Response $response) use ($callable, $next) {
            $result = call_user_func($callable, $request, $response, $next);
            if (!$result instanceof Response) {
                throw new DomainException('Middleware must return instance of Response!');
            }

            return $result;
        };
    }

    /**
     * @param string $path
     *
     * @return callable[]
     */
    protected function getMiddlewareOfRun($path)
    {
        $runs = [];
        foreach ($this->_middleware as $pattern => $callable) {
            if (preg_match($pattern, $path)) {
                $runs[] = $callable;
            }
        }

        return $runs;
    }

    public function __invoke(Request $request, Response $response)
    {
        return $this->runRoute($request, $response);
    }

    protected function routeToPattern($route, $end = '$')
    {
        $replace = '([\w\-_]+)';
        preg_match_all('/{[\w]+}+/', $route, $a);
        foreach ($a[0] as $search) {
            $route = str_replace($search, $replace, $route);
        }
        $route = str_replace('/', '\/', $route);

        return '!^' . $route . $end . '!';
    }
}
