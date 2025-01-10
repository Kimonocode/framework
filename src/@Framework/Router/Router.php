<?php

namespace Infra\Router;

use GuzzleHttp\Psr7\Response;
use Infra\Auth\AuthInterface;
use Infra\Auth\SessionInterface;
use Infra\Errors\Router\InvalidControllerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Infra\Errors\Router\RouteNotFoundException;
use Infra\Kernel;
use Infra\Renderer\RendererInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionFunction;
use ReflectionMethod;

class Router implements RouterInterface
{
    /**
     * Tableau de routes
     * 
     * @var array
     */
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    /**
     * Préfix des routes
     * @var string|null
     */
    private ?string $currentGroupPrefix = null;

    /**
     * Group en cours
     * @var array
     */
    private array $currentGroupMiddlewares = [];

    /**
     * Middlewares globaux
     * @var MiddlewareInterface[]
     */
    private array $globalMiddlewares = [];

    /**
     * Summary of currentRoute
     * @var Route|null
     */
    private ?Route $currentRoute = null;

    /**
     * @inheritDoc
     */
    public function getRoutes(string $method): array
    {
        return $this->routes[$method];
    }

    /**
     * @inheritDoc
     */
    public function group(array $attributes, callable $routes): void
    {
        // Sauvegarde les paramètres actuels
        $previousPrefix = $this->currentGroupPrefix;
        $previousMiddlewares = $this->currentGroupMiddlewares;

        // Applique les nouveaux paramètres
        $this->currentGroupPrefix = ($previousPrefix ?? '') . ($attributes['prefix'] ?? '');
        $this->currentGroupMiddlewares = array_merge($previousMiddlewares, $attributes['middlewares'] ?? []);

        // Exécute les routes du groupe
        $routes($this);

        // Restaure les anciens paramètres
        $this->currentGroupPrefix = $previousPrefix;
        $this->currentGroupMiddlewares = $previousMiddlewares;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name, string $path, callable|array $handler): Route
    {
        $route = new Route(
            'GET',
            $name,
            ($this->currentGroupPrefix ?? '') . $path,
            $handler
        );

        // Ajout des middlewares du groupe
        foreach ($this->currentGroupMiddlewares as $middleware) {
            $route->middleware($middleware);
        }

        $this->routes['GET'][$route->getPath()] = $route;
        return $route;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $name, string $path, array|callable $handler): Route 
    {
        $route = new Route(
            'DELETE',
            $name,
            ($this->currentGroupPrefix ?? '') . $path,
            $handler
        );

        // Ajout des middlewares du groupe
        foreach ($this->currentGroupMiddlewares as $middleware) {
            $route->middleware($middleware);
        }

        $this->routes['DELETE'][$route->getPath()] = $route;
        return $route;
    }
    
    /**
     * @inheritDoc
     */
    public function post(string $name, string $path, array|callable $handler): Route 
    {
        $route = new Route(
            'POST',
            $name,
            ($this->currentGroupPrefix ?? '') . $path,
            $handler
        );

        // Ajout des middlewares du groupe
        foreach ($this->currentGroupMiddlewares as $middleware) {
            $route->middleware($middleware);
        }

        $this->routes['POST'][$route->getPath()] = $route;
        return $route;
    }
    
    /**
     * @inheritDoc
     */
    public function put(string $name, string $path, array|callable $handler): Route 
    {
        $route = new Route(
            'PUT',
            $name,
            ($this->currentGroupPrefix ?? '') . $path,
            $handler
        );

        // Ajout des middlewares du groupe
        foreach ($this->currentGroupMiddlewares as $middleware) {
            $route->middleware($middleware);
        }

        $this->routes['PUT'][$route->getPath()] = $route;
        return $route;
    }

    /**
     * @inheritDoc
     */
    public function addGlobalMiddleware(string $middleware): void
    {
        $this->globalMiddlewares[] = $middleware;
    }

    /**
     * Applique les middlewares.
     *
     * @param array $middlewares
     * @param ServerRequestInterface $request
     * @param callable $finalHandler
     * @throws \RuntimeException
     * @return ResponseInterface
     */
    public function applyMiddlewares(array $middlewares, ServerRequestInterface $request, callable $finalHandler): ResponseInterface
    {
        if (empty($middlewares)) {
            // Aucun middleware restant, appeler le gestionnaire final
            $response = $finalHandler($request);

            // Si le handler retourne un contrôleur ou autre type non conforme, transformer en réponse valide
            if (!$response instanceof ResponseInterface) {
                throw new \RuntimeException("Le final handler doit retourner une instance de Psr\Http\Message\ResponseInterface.");
            }

            return $response;
        }

        $middleware = array_shift($middlewares); // Récupère le premier middleware
        $middlewareInstance = new $middleware();

        if (!$middlewareInstance instanceof MiddlewareInterface) {
            throw new \RuntimeException("Le middleware $middleware doit implémenter MiddlewareInterface.");
        }

        // Crée un RequestHandler anonyme pour enchaîner les middlewares
        $handler = new class($middlewares, $finalHandler, $this) implements \Psr\Http\Server\RequestHandlerInterface {
            private array $middlewares;
            private $finalHandler;
            private $router;

            public function __construct(array $middlewares, callable $finalHandler, Router $router)
            {
                $this->middlewares = $middlewares;
                $this->finalHandler = $finalHandler;
                $this->router = $router;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                // Applique les middlewares restants
                return $this->router->applyMiddlewares($this->middlewares, $request, $this->finalHandler);
            }
        };

        // Appelle le middleware avec la requête et le handler anonyme
        return $middlewareInstance->process($request, $handler);
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

        // Ajout des middlewares globaux
        $middlewares = $this->globalMiddlewares;

        foreach ($this->getRoutes($method) as $route) {
            $this->currentRoute = $route;
            $params = $this->matchRoute($route->getPath(), $uri);
            if ($params !== false) {
                // Ajout des paramètres à la requête
                $request = $request->withAttribute('params', $params);

                // Récupère les middlewares spécifiques à la route
                $middlewares = array_merge($middlewares, $route->getMiddlewares());

                // Définition du handler final
                $handler = function (ServerRequestInterface $req): ResponseInterface {
                    $response = $this->getHandler($this->currentRoute, $req);

                    // Validation explicite pour transformer un retour invalide
                    if (!$response instanceof ResponseInterface) {
                        throw new \RuntimeException("Le handler final doit retourner une instance de Psr\Http\Message\ResponseInterface.");
                    }

                    return $response;
                };

                // Exécute la chaîne de middlewares (globaux + spécifiques à la route)
                return $this->applyMiddlewares($middlewares, $request, $handler);
            }
        }

        // Si aucune route correspondante n'est trouvée, retourne une erreur 404
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
    private function getHandler(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $handler = $route->getHandler();

        if (is_array($handler)) {
            [$controller, $method] = $handler;

            if (!class_exists($controller)) {
                throw new InvalidControllerException("Le contrôleur $controller n'existe pas.");
            }

            if (!method_exists($controller, $method)) {
                throw new InvalidControllerException("La méthode $method n'existe pas dans le contrôleur $controller.");
            }

            $instance = new $controller();
            $response = $this->callHandler([$instance, $method], $request);

            if (!$response instanceof ResponseInterface) {
                throw new \RuntimeException("Le contrôleur {$controller}::{$method} doit retourner une instance de Psr\Http\Message\ResponseInterface.");
            }

            return $response;
        }

        if (is_callable($handler)) {
            $response = $this->callHandler($handler, $request);

            if (!$response instanceof ResponseInterface) {
                throw new \RuntimeException("Le handler doit retourner une instance de Psr\Http\Message\ResponseInterface.");
            }

            return $response;
        }

        throw new InvalidControllerException("Le handler fourni n'est ni un callable valide ni un contrôleur valide.");
    }

    /**
     * Appelle le handler avec des paramètres dynamiques
     *
     * @param  callable|array $handler
     * @param  ServerRequestInterface $request
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

            // Injection des dépendances selon le type attendu
            switch ($type) {
                case ServerRequestInterface::class:
                    $dependencies[] = $request;
                    break;
                case ResponseInterface::class:
                    $dependencies[] = new Response();
                    break;
                case RendererInterface::class:
                    $dependencies[] = Kernel::container()->get(RendererInterface::class);
                    break;
                case AuthInterface::class:
                    $dependencies[] = kernel::container()->get(AuthInterface::class); 
                    break;
                case SessionInterface::class:
                    $dependencies[] = kernel::container()->get(SessionInterface::class); 
                    break;
                default:
                    // Pour tout autre paramètre, on passe null (ou une valeur par défaut si nécessaire)
                    $dependencies[] = null;
                    break;
            }
        }

        return call_user_func_array($handler, $dependencies);
    }
}



