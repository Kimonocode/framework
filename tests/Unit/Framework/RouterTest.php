<?php

namespace Unit\Infra;

use App\Http\Controller\HomeController;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Infra\Errors\Router\RouteNotFoundException;
use Infra\Router\Router;
use PHPUnit\Framework\TestCase;

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
        $router->get('user.show', '/user/{id}', function ($request) {
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


    public function testRouteWhitController()
    {
        $router = new Router();
        $router->get('home.index', '/', [HomeController::class, 'index']);

        $request = new ServerRequest('GET', '/');
        $response = $router->dispatch($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('<h1>Hello World</h1>', (string)$response->getBody());
    }

}