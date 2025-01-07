<?php

namespace App\Http\Controller;

use Infra\Http\Controller\Controller;
use Infra\Renderer\RendererInterface;
use Psr\Http\Message\ResponseInterface;

class HomeController extends Controller {

    public function index(RendererInterface $view): ResponseInterface
    {
        return $view->render('home', ['name' => 'Marie']);
    }

}