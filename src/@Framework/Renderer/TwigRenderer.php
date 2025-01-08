<?php

namespace Infra\Renderer;

use GuzzleHttp\Psr7\Response;
use Infra\Kernel;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigRenderer implements RendererInterface {

        
    /**
     * Twig Loader
     *
     * @var FilesystemLoader
     */
    private $loader;
    
    /**
     * Twig Environment
     *
     * @var Environment
     */
    private $twig;
        
    /**
     * Chemin vers les vues
     *
     * @var string
     */
    private $viewsPath;

    public function __construct()
    {   
        require Kernel::directory('start') . '/kernel.php';
        $this->viewsPath = Kernel::directory('views');
        $this->loader = new FilesystemLoader($this->viewsPath);
        $this->twig = new Environment($this->loader, [
            
        ]);
        foreach($twigFunctions as $function){
            $this->twig->addFunction($function);
        }
    }


    public function render(string $view, array $params = []): ResponseInterface
    {   
        $template = $this->twig->load($view . '.html.twig');
        $content = $template->render($params);
        return new Response(200, [], $content);
    }

    public function setViewsPath(string $viewsPath): void
    {
        $this->viewsPath = $viewsPath;
    }

}