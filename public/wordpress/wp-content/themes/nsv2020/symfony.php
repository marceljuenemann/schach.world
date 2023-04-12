<?php

use Nsv\WebApp\Core\Kernel;
use Symfony\Component\HttpFoundation\Request;

// TODO: move to static method?
// TODO: Pass prod
// TODO: Use Kernel Runner?
$kernel = new Kernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);




/*
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};


<?php
// Handle dynamic routes with our own Symfony-based framework.

use Nsv\WebApp\Kernel;
use NsvWp\Prototype;
use Symfony\Component\HttpFoundation\Request;

//$proto = new Prototype();
//$proto->run();


// TODO: move to static method?
// TODO: Pass prod
$kernel = new Kernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);


// TODO: handle 404
// TODO: handle exceptions
*/