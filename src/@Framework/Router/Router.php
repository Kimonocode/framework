<?php

namespace Infra\Router;

use GuzzleHttp\Psr7\Response;
use Infra\Errors\Router\InvalidControllerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Infra\Errors\Router\RouteNotFoundException;

class Router
{
    /**
     * Tableau des routes
     */
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    /**
     * Renvoie un tableau de toutes les routes enregistrées par leurs méthodes.
     *
     * @param string $method
     * @return Route[]
     */
    public function getRoutes(string $method)
    {
        return $this->routes[$method];
    }

    /**
     * Ajoute une Route GET dans le tableau de routes
     *
     * @param  string $name
     * @param  string $path 
     * @param  callable|array $handler fonction ou controller appelé à l'appel de la route
     * @return Route
     */
    public function get(string $name, string $path, callable|array $handler): Route
    {
        $route = new Route('GET', $name, $path, $handler);
        $this->routes['GET'][$path] = $route;
        return $route;
    }

    /**
     * Parcourt toutes les routes du tableau et appelle la fonction de la route si elle est matchée.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws RouteNotFoundException
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $method = $request->getMethod();

        if (empty($this->getRoutes($method))) {
            throw new RouteNotFoundException("Aucune route définie pour la méthode $method.");
        }

        foreach ($this->getRoutes($method) as $route) {
            $params = $this->matchRoute($route->getPath(), $uri);
            if ($params !== false) {
                // Ajout des paramètres à la requête
                $request = $request->withAttribute('params', $params);
                return $this->getHandler($route, $request);
            }
        }

        // Si aucune route ne correspond
        return new Response(404, ['Content-Type' => 'text/plain'], '404 Not Found');
    }

    /**
     * Compare une route dynamique avec une URI et extrait les paramètres.
     *
     * @param string $routePath
     * @param string $uri
     * @return array|false
     */
    private function matchRoute(string $routePath, string $uri): array|false
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $uriParts = explode('/', trim($uri, '/'));

        if (count($routeParts) !== count($uriParts)) {
            return false;
        }

        $params = [];

        foreach ($routeParts as $index => $part) {
            if (preg_match('/^\{(\w+)\}$/', $part, $matches)) {
                // Paramètre dynamique détecté, on extrait la valeur
                $params[$matches[1]] = $uriParts[$index];
            } elseif ($part !== $uriParts[$index]) {
                // Les segments statiques ne correspondent pas
                return false;
            }
        }

        return $params;
    }

    /**
     * Vérifie si le handler de la route est un callable ou une class Controller
     *
     * @param  Route $route
     * @param  ServerRequestInterface $request
     * @return mixed
     * @throws InvalidControllerException
    */
    private function getHandler(Route $route, ServerRequestInterface $request): mixed
    {
        $handler = $route->getHandler();

        // Gestion du handler sous forme de tableau [Controller::class, 'method']
        if (is_array($handler)) {
            [$controller, $method] = $handler;

            // Vérification de l'existence du contrôleur
            if (!class_exists($controller)) {
                throw new InvalidControllerException("Le contrôleur $controller n'existe pas.");
            }

            // Vérification de l'existence de la méthode
            if (!method_exists($controller, $method)) {
                throw new InvalidControllerException("La méthode $method n'existe pas dans le contrôleur $controller.");
            }

            // Instanciation du contrôleur
            $instance = new $controller();

            if (!is_callable([$instance, $method])) {
                throw new InvalidControllerException("La méthode $method du contrôleur $controller n'est pas callable.");
            }

            return call_user_func([$instance, $method], $request, new Response());
        }

        // Gestion du handler sous forme de fonction anonyme ou callable
        if (is_callable($handler)) {
            return call_user_func($handler, $request, new Response());
        }

        throw new InvalidControllerException("Le handler fourni n'est ni un callable valide ni un contrôleur valide.");
    }

}   



