<?php

namespace Infra\Http\Controller;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Controller {

    public function __construct()
    {

    }
        
    /**
     * Retourne une réponse 404 Notfound
     *
     * @param  string $message
     * @return ResponseInterface
     */
    public function notFound(?string $message): ResponseInterface
    {   
        return new Response(404, [], $message ?? '404 Notfound');
    }

    /**
     * Retourne une réponse 403 Forbidden
     * @return ResponseInterface
     */
    public function forbidden(): ResponseInterface
    {
        return new Response(403, [], '403 Forbidden');  
    }

    /**
     * Retourne une réponse 401 Unauthorized
     * @return ResponseInterface
     */
    public function unauthorized(): ResponseInterface
    {
        return new Response(401, [], '401 Unauthorized');
    }
}