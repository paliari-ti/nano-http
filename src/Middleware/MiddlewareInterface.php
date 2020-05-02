<?php

namespace Paliari\NanoHttp\Middleware;

use Paliari\NanoHttp\Http\Request;
use Paliari\NanoHttp\Http\Response;

interface MiddlewareInterface
{
    /**
     * @param Request  $request
     * @param Response $response
     * @param callable $next Any callable that accepts three arguments:
     *                           1. A Request object
     *                           2. A Response object
     *                       return Response object
     *
     * @return Response
     */
    public function __invoke($request, $response, $next);
}
