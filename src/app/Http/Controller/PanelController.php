<?php

namespace App\Http\Controller;

use App\Model\User;
use Infra\Auth\AuthInterface;
use Infra\Http\Controller\Controller;
use Infra\Renderer\RendererInterface;
use Psr\Http\Message\ResponseInterface;

class PanelController extends Controller {

    public function index(AuthInterface $auth, RendererInterface $view): ResponseInterface
    {   
        /**
         * @var User|null $user
         */
        $user = $auth->getCurrentUser();

        // Check if user is authenticated
        if ($user) {
            // Render user panel with user data
            return $view->render('user.panel', (array) $user);
        }

        // Redirect to login with 401 Unauthorized status
        return $this->redirectToView('login', 401);
    }

}