<?php

namespace App\Http\Controller;

use Infra\Http\Controller\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserController extends Controller {


    public function show(ServerRequestInterface $request, ResponseInterface $response)
    {
        $id = $request->getAttribute('params')['id'];
        $response->getBody()->write("<h1>Hello user whit ID: $id</h1>");
        return $response->withStatus(200);
    }

}