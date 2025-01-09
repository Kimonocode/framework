<?php

use App\Http\Controller\AuthController;
use App\Http\Controller\PanelController;
use Infra\Router\RouterInterface;
use App\Http\Controller\HomeController;
use App\Http\Controller\RegisterController;
use App\Http\Controller\UserController;

$router = Infra\Kernel::container()->get(RouterInterface::class);

/**
 * Enregistrement des routes dans ce fichier
 */

$router->get('home', '/', [HomeController::class, 'index']);

$router->get('login', '/login', [AuthController::class, 'index']);
$router->post('login','/login', [AuthController::class, 'login']);

$router->get('register', '/register', [RegisterController::class, 'index']);
$router->post('register', '/register', [RegisterController::class, 'register']);

$router->get('user.panel', '/dashboard', [PanelController::class, 'index']);

$router->get('user.show', '/user/{id}', [UserController::class, 'show']);



