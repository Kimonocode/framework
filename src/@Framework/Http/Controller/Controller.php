<?php

namespace Infra\Http\Controller;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Controller {

    private int $code = 200; // Default to HTTP 200 OK
    private string $message = 'OK';
    private array|string $data = [];

    public function __construct()
    {
    }
    
    /**
     * Prépare une Response 404 Not Found 
     * @return \Infra\Http\Controller\Controller
     */
    public function notFound(): self
    {   
        $this->code = 404;
        $this->message = "Not Found";
        $this->data = [];
        return $this;
    }

    /**
     * Prépare une Response 403 Forbidden
     * 
     * @return \Infra\Http\Controller\Controller
     */
    public function forbidden(): self
    {
        $this->code = 403;
        $this->message = "Forbidden";
        $this->data = [];
        return $this;
    }

    /**
     * Prépare une Response 401 Unauthorized
     * 
     * @return \Infra\Http\Controller\Controller
     */
    public function unauthorized(): self
    {
        $this->code = 401;
        $this->message = "Unauthorized";
        $this->data = [];
        return $this;
    }

    /**
     * Prépare une Response 400 Bad Request
     * 
     * @param string|array $errors
     * @return \Infra\Http\Controller\Controller
     */
    public function badRequest(string|array $errors): self
    {        
        $this->code = 400;
        $this->message = "Bad Request";
        $this->data = $errors;
        return $this;
    }

    /**
     * Retourne une Response au format JSON
     * 
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function toJson(): ResponseInterface
    {
        $json = [
            "status" => $this->code,
            "message" => $this->message,
            "data" => $this->data
        ];

        return new Response(
            $this->code, 
            ['Content-Type' => 'application/json'], 
            json_encode($json, JSON_UNESCAPED_UNICODE)
        );
    }
}
