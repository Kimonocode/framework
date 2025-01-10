<?php

use App\Http\Controller\AuthController;
use App\Http\Controller\DashboardController;
use Infra\Router\RouterInterface;
use App\Http\Controller\HomeController;
use App\Http\Controller\RegisterController;
use App\Http\Controller\TestController;
use Infra\Http\Middleware\AuthMiddleware;
use Middlewares\TrailingSlash;
use Middlewares\Whoops;

$router = Infra\Kernel::container()->get(RouterInterface::class);

/**
 * Enregistrement des middlewares globaux 
 */

$router->addGlobalMiddleware(Whoops::class);
$router->addGlobalMiddleware(TrailingSlash::class);


/**
 * Enregistrement des routes
 */

$router->get('test', '/test', [TestController::class, 'test']);

$router->get('home', '/', [HomeController::class, 'index']);

$router->get('login', '/login', [AuthController::class, 'index']);
$router->get('logout', '/logout', [AuthController::class,'logout']);
$router->post('login','/login', [AuthController::class, 'login']);

$router->get('register', '/register', [RegisterController::class, 'index']);
$router->post('register', '/register', [RegisterController::class, 'register']);

$router->group([
    'prefix' => '/dashboard',
    'middlewares' => [AuthMiddleware::class]
], function(RouterInterface $router){

    $router->get('dashboard', '/', [DashboardController::class, 'index']);

});


