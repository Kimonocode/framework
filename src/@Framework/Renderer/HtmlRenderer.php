<?php

namespace Infra\Renderer;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class HtmlRenderer implements RendererInterface {

    /**
     * Dossier des vues
     *
     * @var string viewsPath
     */
    private $viewsPath;

    public function __construct()
    {
        $this->viewsPath = dirname($_SERVER['SCRIPT_FILENAME'], 2) . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "views";
    }

    /**
     * Retourne une réponse 200 avec une vue html
     * @param  string $view
     * @param  array $params
     * @return ResponseInterface
     */
    public function render(string $view, array $params = []): ResponseInterface
    {   
        if(!empty($params)){
            extract($params, EXTR_OVERWRITE);
        }

        ob_start();
        require $this->viewsPath . DIRECTORY_SEPARATOR ."$view.html.php";
        $content = ob_get_clean();
        
        $response = new Response();
        $response->getBody()->write($content);

        return $response->withStatus(200);
    }

    /**
     * Change le dossier des vues
     *
     * @param  string $viewsPath
     * @return void
     */
    public function setViewsPath($viewsPath): void
    {
        $this->viewsPath = $viewsPath;
    }
}