<?php

namespace Nsv\WebApp\Core;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * An API error with a message that can be shown to the user, so it should be in German.
 */
class NsvErrorException extends HttpException
{
  function __construct(string $message) {
    parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, $message);
  }
}
