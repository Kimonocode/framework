<?php

namespace App\Http\Controller;

use GuzzleHttp\Psr7\ServerRequest;
use Infra\Http\Controller\Controller;
use Psr\Http\Message\ResponseInterface;

class HomeController extends Controller {

    /**
     * Affiche la page d'acceuil
     *
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface $response
     * @return ResponseInterface
     */
    public function index(ServerRequest $request, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write('<h1>Hello World</h1>');
        return $response->withStatus(200);
    }

}