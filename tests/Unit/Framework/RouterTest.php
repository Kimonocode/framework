<?php

namespace Unit\Infra;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Infra\Errors\Router\RouteNotFoundException;
use Infra\Router\Router;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RouterTest extends TestCase
{
    public function testRouteWithGetMethod()
    {
        $router = new Router();
        $router->get('Home', '/', function () {
            return new Response(200, [], 'Hello World');
        });

        $request = new ServerRequest('GET', '/');
        $response = $router->dispatch($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello World', (string)$response->getBody());
    }

    public function testRouteWithDynamicParameter()
    {
        $router = new Router();
        $router->get('user.show', '/user/{id}', function (ServerRequestInterface $request) {
            $params = $request->getAttribute('params');
            return new Response(200, [], "User ID: " . $params['id']);
        });

        $request = new ServerRequest('GET', '/user/42');
        $response = $router->dispatch($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('User ID: 42', (string)$response->getBody());
    }

    public function testRouteIsNotFound()
    {
        $this->expectException(RouteNotFoundException::class);
        $router = new Router();
        $request = new ServerRequest('GET', '/azeazeze');

        $router->dispatch($request);
    }

}