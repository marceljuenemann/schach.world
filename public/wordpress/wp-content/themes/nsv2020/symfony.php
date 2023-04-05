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
