<?php

namespace App\Http\Controller;

use Infra\Auth\AuthInterface;
use Infra\Auth\SessionInterface;
use Infra\Http\Controller\Controller;
use Psr\Http\Message\ResponseInterface;

class TestController extends Controller {

    public function test(AuthInterface $auth, SessionInterface $session): ResponseInterface
    {   
        $authenticated = $auth->isAuthenticated();

        return $this->badRequest($authenticated === true)->toJson();
    }

}