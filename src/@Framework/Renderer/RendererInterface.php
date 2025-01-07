<?php

namespace Infra\Renderer;

use Psr\Http\Message\ResponseInterface;

interface RendererInterface {
    
    /**
     * Retourne une réponse 200 avec une vue html
     * @param  string $view
     * @param  array $params
     * @return ResponseInterface
     */
    public function render(string $view, array $params = []): ResponseInterface;

     /**
     * Change le dossier des vues
     *
     * @param  string $viewsPath
     * @return void
     */
    public function setViewsPath(string $viewsPath): void;

}