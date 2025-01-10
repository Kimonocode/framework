<?php

namespace App\Http\Controller;

use App\Model\User;
use App\Validator\RegisterValidator;
use Infra\Auth\AuthInterface;
use Infra\Auth\SessionInterface;
use Infra\Http\Controller\Controller;
use Infra\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegisterController extends Controller {
    
    public function index(RendererInterface $view)
    {
        return $view->render('register');
    }
   
    public function register(
        ServerRequestInterface $request,
        SessionInterface $session,
        AuthInterface $auth
    ) 
    {
        $data = $this->getFormData($request);
        $validator = new RegisterValidator();

        if(!$validator->validate( $data, $request)) {
            return $this->badRequest($validator->getErrors())->toJson();
        }   

        $user = new User();
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->email = $data['email'];
        $user->password = User::hashPassword( $data['password'] );
        $user->role = 'user';
        $user->created_at = date('Y-m-d H:i:s');

        $id = $user->save();

        if(!$id){
            $this->badRequest('Erreur interne; enregistrement impossible')->toJson();
        }

        $auth->authenticate($id);
        $session->flash('sussess', ["message" => "Enregistré avec succès."]);

        return $this->redirectToView('dashboard');
    }
}