<?php

use GuzzleHttp\Psr7\ServerRequest;
use function Http\Response\send;

require dirname(__DIR__) . '/vendor/autoload.php';

try {

$kernel= new Infra\Kernel();

$response = $kernel->run(ServerRequest::fromGlobals());

send($response);

} catch (Throwable $e) {
    // Gestion des erreurs non interceptées
    $errorResponse = new \GuzzleHttp\Psr7\Response(
        500,
        ['Content-Type' => 'text/plain'],
        'Une erreur interne est survenue: ' . $e->getMessage()
    );

    send($errorResponse);
}