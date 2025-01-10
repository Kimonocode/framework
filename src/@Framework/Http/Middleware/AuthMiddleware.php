<?php

namespace Infra\Http\Middleware;

use GuzzleHttp\Psr7\Response;
use Infra\Auth\AuthInterface;
use Psr\Http\Server\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface {

    protected string $redirect = '/login';
    /**
     * @inheritDoc
     */
    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface 
    {
        $isAuthenticated = \Infra\Kernel::container()->get(AuthInterface::class)->isAuthenticated();
        if(!$isAuthenticated){
            return (new Response())
                ->withStatus(401)
                ->withHeader('Location', $this->redirect);
        }
        return $handler->handle($request);
    }
}