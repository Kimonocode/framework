<?php

namespace Infra\Router;

use Infra\Errors\Router\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface {
    
    /**
     * Enregistre une route en GET
     *
     * @param  string $name Nom de la route
     * @param  string $path CHemin de la route
     * @param  callable|array $handler Function ou Controller à appeler
     * @return Route
     */
    public function get(string $name, string $path, callable|array $handler): Route;  

    /**
     * Enregistre une route POST
     *
     * @param  string $name Nom de la route
     * @param  string $path Chemin de la route
     * @param  callable|array $handler Function ou Controller à appeler
     * @return Route
     */
    public function post(string $name, string $path, callable|array $handler): Route;

    /**
     * Enregistre une route PUT
     *
     * @param  string $name Nom de la route
     * @param  string $path CHemin de la route
     * @param  callable|array $handler Function ou COntroller à appeler
     * @return Route
     */
    public function put(string $name, string $path, callable|array $handler): Route;  

    /**
     * Enregistre une route DELETE
     *
     * @param  string $name Nom de la route
     * @param  string$path Chemin de la roure
     * @param  callable|array $handler Function ou Controller à appeler
     * @return Route
     */
    public function delete(string $name, string $path, callable|array $handler): Route;

    /**
     * Parcourt toutes les routes du tableau et appelle la fonction de la route si elle est matchée.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws RouteNotFoundException
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;
}