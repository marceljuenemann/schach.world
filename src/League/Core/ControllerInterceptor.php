<?php

namespace Nsv\League\Core;

use Nsv\League\Controller\AbstractLeagueController;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Intercepts Controller calls for various magical things :)
 */
class ControllerInterceptor
{
  function __construct(private LeagueRepository $leagueRepository) {}

  private ?AbstractLeagueController $controller = null;

  #[AsEventListener]
  public function onControllerEvent(ControllerEvent $event) {
    $controller = $event->getController();
    if (is_array($controller)) $controller = $controller[0];

    if ($controller instanceof AbstractLeagueController) {
      // Remember the controller for enhanced error handling.
      $this->controller = $controller;
    }
  }

  /**
   * Intercept exceptions thrown by an AbstractLeagueController in order
   * to show nicer error pages with the template for the league.
   */
  #[AsEventListener]
  public function onExceptionEvent(ExceptionEvent $event) {
    if (!$this->controller) return;

    $exception = $event->getThrowable();
    $response = $this->controller->errorResponse($exception);
    if (!$response) return;

    if ($exception instanceof HttpExceptionInterface) {
      $response->setStatusCode($exception->getStatusCode());
      $response->headers->replace($exception->getHeaders());
    } else {
      $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    $event->setResponse($response);
  }
}
