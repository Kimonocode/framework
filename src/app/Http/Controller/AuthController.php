<?php

namespace App\Http\Controller;

use Infra\Http\Controller\Controller;
use Infra\Renderer\RendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller {

    public function index(RendererInterface $view): ResponseInterface
    {
        return $view->render('auth/login');
    }

    public function login(ServerRequestInterface $request)
    {
        
    }

}