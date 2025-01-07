<?php

namespace Infra\Renderer;

use Psr\Http\Message\ResponseInterface;

interface RendererInterface {

    public function render(string $view, array $params = []): ResponseInterface;

    public function setViewsPath(string $viewsPath): void;

}