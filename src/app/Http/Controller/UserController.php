<?php

namespace App\Http\Controller;

use Infra\Http\Controller\Controller;
use Infra\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController extends Controller {


    public function show(ServerRequestInterface $request, RendererInterface $view)
    {
        return $view->render('user', ['id' => $request->getAttribute('params')['id']]);
    }
}