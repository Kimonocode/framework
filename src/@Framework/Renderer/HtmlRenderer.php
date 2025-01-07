<?php

namespace Infra\Renderer;

use GuzzleHttp\Psr7\Response;
use Infra\Kernel;
use Psr\Http\Message\ResponseInterface;

class HtmlRenderer implements RendererInterface {

    /**
     * Dossier des vues
     *
     * @var string viewsPath
     */
    private $viewsPath;

    /**
     * Container de functions utlisées dans les vues
     *
     * @var array
     */
    private $htmlFunctions = [];

    public function __construct()
    {
        require Kernel::directory('start') . '/kernel.php';
        $this->viewsPath = Kernel::directory('views');
        $this->htmlFunctions = $viewsFunctions;
    }

    /**
     * Retourne une réponse 200 avec une vue html
     * @param  string $view
     * @param  array $params
     * @return ResponseInterface
     */
    public function render(string $view, array $params = []): ResponseInterface
    {   
        // extrait les paramètres pour les passer à la vues
        if(!empty($params)){
            extract($params, EXTR_OVERWRITE);
        }
        // extrait les functions
        if(!empty($this->htmlFunctions)){
            foreach($this->htmlFunctions as $name => $callback){
                $$name = $callback;
            }
        }

        ob_start();
        require $this->viewsPath . "/$view.html.php";
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