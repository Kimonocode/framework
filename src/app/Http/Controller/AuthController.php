<?php

namespace App\Http\Controller;

use Infra\Auth\AuthInterface;
use Infra\Http\Controller\Controller;
use Infra\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller {

    public function index(RendererInterface $view)
    {
        return $view->render('auth/login');
    }

    public function login(ServerRequestInterface $request)
    {
        
    }

    public function logout(AuthInterface $auth)
    {
        if($auth->logout()){
            return $this->redirectToView('login');
        }
        return $this->badRequest('Impossible de si déconnecter')->toJson();
    }

}