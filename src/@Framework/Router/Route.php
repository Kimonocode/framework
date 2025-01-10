<?php

namespace Infra\Router;

use Psr\Http\Server\MiddlewareInterface;

class Route {
    
    /**
     * method
     *
     * @var string GET | POST
     */
    private $method;
        
    /**
     * name
     *
     * @var string
     */
    private $name;
    
    /**
     * path
     *
     * @var string
     */
    private $path;
    
    /**
     * hanlder
     *
     * @var callable|array
     */
    private $handler;

    /**
     * Tableau de middlewares
     * @var MiddlewareInterface[]
     */
    private array $middlewares = [];

    public function __construct(string $method, string $name, string $path, callable|array $handler)
    {
        $this->method = $method;
        $this->name = $name;
        $this->path = $path;
        $this->handler = $handler;
    }
    
    /**
     * Renvoie la méthode de la route
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
    
    /**
     * Renvoie le nom de la route
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Renvoie le chemin de la route
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
    
    /**
     * Renvoie la fonction à appeler 
     *
     * @return callable|array
     */
    public function getHandler(): callable|array
    {
        return $this->handler;
    }

    /**
     * Ajoute un middleware dans la liste
     * 
     * @param string $middleware
     * @return Route
     */
    public function middleware(string $middleware)
    {
        if (!is_subclass_of($middleware, MiddlewareInterface::class)) {
            throw new \InvalidArgumentException("Le middleware doit implémenter MiddlewareInterface.");
        }
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Renvoie la liste des middlewares associés à cette route
     *
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

}