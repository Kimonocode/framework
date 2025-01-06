<?php

use App\Http\Controller\HomeController;
use App\Http\Controller\UserController;
use Infra\Router\Router;

$router = new Router();

/**
 * Enregistrement des routes dans ce fichier
 */

$router->get('home.index', '/', [HomeController::class, 'index']);

$router->get('user.show', '/user/{id}', [UserController::class, 'show']);


