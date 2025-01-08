<?php

namespace App\Http\Controller;

use App\Validator\RegisterValidator;
use Infra\Http\Controller\Controller;
use Infra\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegisterController extends Controller {
    
    public function index(RendererInterface $view)
    {
        return $view->render('register');
    }
   
    public function register(ServerRequestInterface $request) 
    {
        $data = $request->getParsedBody();
        $validator = new RegisterValidator();

        if(!$validator->validate($data)){
           return $this->badRequest($validator->getErrors())->toJson();
        }
    }
}