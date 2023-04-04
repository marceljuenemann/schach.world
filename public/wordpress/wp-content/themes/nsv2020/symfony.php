<?php
// Handle dynamic routes with our own Symfony-based framework.

use NsvWp\Prototype;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

$proto = new Prototype();
$proto->run();

// TODO: handle 404
// TODO: handle exceptions
