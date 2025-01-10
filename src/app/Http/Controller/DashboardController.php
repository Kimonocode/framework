<?php

namespace App\Http\Controller;

use Infra\Auth\AuthInterface;
use Infra\Http\Controller\Controller;
use Infra\Renderer\RendererInterface;
use Psr\Http\Message\ResponseInterface;

class DashboardController extends Controller {

    public function index(AuthInterface $auth, RendererInterface $view): ResponseInterface
    {   
        return $view->render('dashboard', (array) $auth->getCurrentUser());
    }

}