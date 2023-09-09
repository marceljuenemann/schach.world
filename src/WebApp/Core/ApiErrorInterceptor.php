<?php

namespace Nsv\WebApp\Core;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Intercepts exceptions for /api/ routes and returns them as JSON objects.
 */
class ApiErrorInterceptor
{
  function __construct() {}

  #[AsEventListener]
  public function onExceptionEvent(ExceptionEvent $event) {
    // TODO: Create isApiRequest util somewhere.
    if (!str_contains($event->getRequest()->getUri(), '/api/')) return;

    $exception = $event->getThrowable();
    // TODO: handle NsvError
    // TODO: convert all HttpExceptions. Include message + debug if admin. 
    if ($exception instanceof HttpException && $exception->getPrevious() instanceof ValidationFailedException) {
      $event->setResponse($this->handleValidationFailure($exception->getPrevious()));
    }
  }

  private function handleValidationFailure(ValidationFailedException $exception): Response {
    $violations = [];
    foreach ($exception->getViolations() as $violation) {
      $model = new \stdClass;
      $model->message = $violation->getMessage();
      $violations[$violation->getPropertyPath()][] = $model;
    }

    $response = new JsonResponse([
      'errorType' => 'nsv',
      'errorMessages' => [],
      'validationErrors' => $violations
    ]);
    $response->setEncodingOptions(JSON_PRETTY_PRINT);
    $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    return $response;
  }
}
