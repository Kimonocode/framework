<?php

use Infra\Router\RouterInterface;
use App\Http\Controller\HomeController;
use App\Http\Controller\UserController;

$router = Infra\Kernel::container()->get(RouterInterface::class);

/**
 * Enregistrement des routes dans ce fichier
 */

$router->get('home.index', '/', [HomeController::class, 'index']);

$router->get('user.show', '/user/{id}', [UserController::class, 'show']);


