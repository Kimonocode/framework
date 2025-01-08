<?php

use Infra\Renderer\RendererInterface;
use Infra\Renderer\TwigRenderer;
use Infra\Router\Router;
use Infra\Router\RouterInterface;


return [
    RouterInterface::class => \DI\get(Router::class),
    RendererInterface::class => \DI\get(TwigRenderer::class),
];