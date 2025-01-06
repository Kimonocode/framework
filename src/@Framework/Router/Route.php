<?php

namespace Infra\Router;

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

}