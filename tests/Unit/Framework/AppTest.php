<?php

namespace Unit\Infra;

use GuzzleHttp\Psr7\ServerRequest;
use Infra\App;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase {


    public function testAppRunWhitRequestAndResponse(){
        $app = new App();
        $request = new ServerRequest('GET', '/');

        $response = $app->run($request);

        $this->assertSame(200, $response->getStatusCode());

    }

}