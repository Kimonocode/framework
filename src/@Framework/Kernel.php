<?php

namespace Infra;

use Exception;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Kernel
{   

    /**
     * Lance l'application et retourne une réponse.
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {

        require_once(self::root() . '/start/routes.php'); 

        // Tente de dispatcher la requête
        $response = $router->dispatch($request);

        // Retourne la réponse si elle existe
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        // Retourne une réponse 404 si aucune route n'a été trouvée
        return new Response(404, ['Content-Type' => 'text/plain'], '404 - Not Found');
    }
        
    /**
     * Retourne le chemin du dossier de l'application
     * @return string
     */
    public static function root(): string
    {
        return dirname($_SERVER['SCRIPT_FILENAME'], 2);
    }

    public static function directory(string $key): string
    {   
        $bootFile = self::root() . '/start/kernel.php';

        if(!file_exists($bootFile)){
            throw new Exception("Le fichier $bootFile n'existe pas.");
        }

        require $bootFile;

        if(!array_key_exists($key, $directories)){
            throw new InvalidArgumentException("$key n'est pas déffini dans le fichier start/kernel.php");
        }

        return $directories[$key];
    }
}