<?php

use Infra\Kernel;
use Infra\Router\RouterInterface;
use Twig\TwigFunction;

define('VIEWS_PATH', Kernel::root() . '/resources/views');

/**
 * Tableau des différens dossiers de l'application
 * @var array
 */
$directories = [
    'views' => VIEWS_PATH,
    'start' =>  Kernel::root() . '/start',
    'config' => Kernel::root() . '/config',
];

/**
 * Fonctions utilisées pour HtmlRenderer
 * @var array 
 */
$htmlFunctions = [
    'template' => function(string $template): string {
        $path = VIEWS_PATH . "/$template" . '.html.php';
        ob_start();
        require_once($path);
        return ob_get_clean();
    }
];

/**
 * Fonctions utilisées pour le TwigRenderer
 * @var TwigFunction[]
 */
$twigFunctions = [
    // Crée une url dynamique à partir du nom d'une route
    new TwigFunction('route', function (string $name, array $params = []) {
        $router = Kernel::container()->get(RouterInterface::class);
        
        foreach ($router->getRoutes('GET') as $route) {
            if ($route->getName() === $name) {
                $path = $route->getPath();
    
                // Remplace les paramètres dynamiques dans l'URL
                foreach ($params as $key => $value) {
                    $path = str_replace("{{$key}}", $value, $path);
                }
    
                return $path;
            }
        }
    
        throw new InvalidArgumentException("La route '$name' n'est pas enregistrée dans le container de routes.");
    })
];