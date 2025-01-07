<?php

use Infra\Kernel;

define('VIEWS_PATH', Kernel::root() . '/resources/views');

$directories = [
    'start' =>  Kernel::root() . '/start',
    'views' => VIEWS_PATH 
];

$viewsFunctions = [
    'template' => function(string $template): string {
        $path = VIEWS_PATH . "/$template" . '.html.php';
        ob_start();
        require_once($path);
        return ob_get_clean();
    }
];