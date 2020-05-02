# nano-http
The fastest http php framework

## How to Install

```bash
composer require paliari/nano-http

```
### Usage

```php
<?php
require 'vendor/autoload.php';

```

## Routing

HTTP method: GET, POST, PUT, PATCH, DELETE
### Mapping

```php

//...

$app = new \Paliari\NanoHttp\App();

$app->get('/', function ($req, \Paliari\NanoHttp\Http\Response $res) {
    $res->body = json_encode(['message' => 'Welcome']);

    return $res;
});
$app->post('/person', function ($req, \Paliari\NanoHttp\Http\Response $res) {
    $params = $req->post();
    $person = yourMethodCreatePerson($params);
    $res->body = json_encode($person);

    return $res;
});
$app->get('/person/{id}', function ($req, \Paliari\NanoHttp\Http\Response $res, $id) {
    $person = yourMethodGetPerson($id);
    $res->body = json_encode($person);

    return $res;
});

```
### Custom map

App->map(method: string, route: string, callable: string|callable)
if the callable is string it must be separated by ":" eg: "ClassName:metodName".

```php
$app->map($method, $route, $callable);

```
## Middleware

### Using MiddlewareInterface

```php
<?php

class AuthMiddleware implements \Paliari\NanoHttp\Middleware\MiddlewareInterface
{
    public function __invoke($request, $response, $next)
    {
        // TODO: Implement __invoke() method.
    }
}

```
```php
<?php

//...

$authMiddleware = new AuthMiddleware();
$aclMiddleware = new AclMiddleware();
$customMiddleware = new CustomMiddleware();
$app->add($customMiddleware, '/custom/path')
    ->add($aclMiddleware, '/')
    ->add($authMiddleware, '/')
;

```
