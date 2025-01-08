<?php

namespace Infra\Router;

use GuzzleHttp\Psr7\Response;
use Infra\Errors\Router\InvalidControllerException;
use Infra\Kernel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Infra\Errors\Router\RouteNotFoundException;
use ReflectionFunction;
use ReflectionMethod;

class Router implements RouterInterface
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
     * @inheritDoc
     */
    public function get(string $name, string $path, callable|array $handler): Route
    {
        $route = new Route('GET', $name, $path, $handler);
        $this->routes['GET'][$path] = $route;
        return $route;
    }

    /**
     * @inheritDoc
     */
    public function post(string $name, string $path, array|callable $handler): Route 
    {
        return new Route('POST', 'post', '/', function(){});

    }
 
    /**
     * @inheritDoc
     */
    public function put(string $name, string $path, array|callable $handler): Route 
    {
        return new Route('PUT', 'put', '/', function(){});

    }

    /**
     * @inheritDoc
     */
    public function delete(string $name, string $path, array|callable $handler): Route 
    {
        return new Route('DELETE', 'delete', '/', function(){});
    }

    /**
     * @inheritDoc
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
     * Vérifie si le handler de la route est un callable ou une classe Controller
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

            if (!class_exists($controller)) {
                throw new InvalidControllerException("Le contrôleur $controller n'existe pas.");
            }

            if (!method_exists($controller, $method)) {
                throw new InvalidControllerException("La méthode $method n'existe pas dans le contrôleur $controller.");
            }

            $instance = new $controller();

            return $this->callHandler([$instance, $method], $request);
        }

        // Gestion du handler sous forme de fonction anonyme ou callable
        if (is_callable($handler)) {
            return $this->callHandler($handler, $request);
        }

        throw new InvalidControllerException("Le handler fourni n'est ni un callable valide ni un contrôleur valide.");
    }

    /**
     * Appelle le handler avec des paramètres dynamiques via le conteneur
     *
     * @param callable|array $handler
     * @param ServerRequestInterface $request
     * @return mixed
     */
    private function callHandler(callable|array $handler, ServerRequestInterface $request): mixed
    {
        $reflection = is_array($handler)
            ? new ReflectionMethod($handler[0], $handler[1])
            : new ReflectionFunction($handler);

        $dependencies = [];

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType()?->getName();

            if ($type) {
                // Si une dépendance est définie, la récupérer depuis le conteneur
                if (Kernel::container()->has($type)) {
                $dependencies[] = Kernel::container()->get($type);
                } elseif ($type === ServerRequestInterface::class) {
                    $dependencies[] = $request;
                } elseif ($type === ResponseInterface::class) {
                    $dependencies[] = new Response();
                } else {
                    // Tentative d'instanciation automatique
                    try {
                        $dependencies[] = new $type();
                    } catch (\Throwable $e) {
                        throw new \InvalidArgumentException("Impossible d'instancier le paramètre '{$parameter->getName()}' de type '{$type}'");
                    }
                }
            } elseif ($parameter->isDefaultValueAvailable()) {
                // Utiliser la valeur par défaut si disponible
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                // Sinon passer null
                $dependencies[] = null;
            }
        }

        return call_user_func_array($handler, $dependencies);
    }
}



