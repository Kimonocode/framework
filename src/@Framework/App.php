<?php

namespace Infra;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class App
{
    
    public function __construct()
    {

    }

    /**
     * Lance l'application et retourne une réponse.
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {

        require_once(dirname(__DIR__, 2) . '/start/routes.php'); 

        // Tente de dispatcher la requête
        $response = $router->dispatch($request);

        // Retourne la réponse si elle existe
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        // Retourne une réponse 404 si aucune route n'a été trouvée
        return new Response(404, ['Content-Type' => 'text/plain'], '404 - Not Found');
    }
}