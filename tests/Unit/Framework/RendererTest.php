<?php

namespace Unit\Infra;

use Infra\Renderer\HtmlRenderer;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase {

    public function testRendererCanRenderView()
    {
        $renderer = new HtmlRenderer();
        $renderer->setViewsPath(__DIR__ . '/views');

        $response = $renderer->render('index');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertEquals('<h1>Hello World</h1>', (string)$response->getBody());
    }

}