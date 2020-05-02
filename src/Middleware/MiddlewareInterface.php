<?php

namespace Paliari\NanoHttp\Middleware;

interface MiddlewareInterface
{
    public function __invoke($request, $response, $next);
}
