<?php

namespace Infra\Http\Controller;

use GuzzleHttp\Psr7\Response;
use Infra\Helpers\Stringify;
use Infra\Kernel;
use Infra\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    /**
     * Converti les champs html camelCase en snakeCase
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    public function getFormData(ServerRequestInterface $request): array
    {
        $data = $request->getParsedBody();
        $formattedData = [];

        foreach ($data as $key => $value) {
            $formattedData[Stringify::camelToSnakeCase($key)] = $value;
        }
        return $formattedData;
    }

    /**
     * Redirige vers une vue
     * 
     * @param string $name
     * @param ?int $code
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function redirectToView(string $name, ?int $code = 301): ResponseInterface
    {
        $router = Kernel::container()->get(RouterInterface::class);

        // Parcourir les routes définies pour le GET
        foreach ($router->getRoutes('GET') as $route) {
            if ($route->getName() === $name) {
                // Récupérer l'URL associée à la route et rediriger
                $url = $route->getPath();
                return new Response($code, ['Location' => $url]);
            }
        }

        // Si la route n'est pas trouvée, une exception ou une réponse alternative peut être retournée
        throw new \RuntimeException("La route nommée '{$name}' n'a pas été trouvée.");
    }
}
