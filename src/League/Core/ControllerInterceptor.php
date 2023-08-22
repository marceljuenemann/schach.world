<?php

namespace Nsv\League\Core;

use Nsv\League\Controller\AbstractLeagueController;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
      // Fetch the league specified in the URL.
      if ($event->getRequest()->attributes->has('league')) {
        $leagueName = $event->getRequest()->attributes->get('league');
        $league = $this->leagueRepository->findByPath($leagueName);
        if (!$league) {
          throw new NotFoundHttpException("League not found");
        }
        $controller->league = $league;

        // Optimization: Fetch all divisions and teams.
        $league->divisions->toArray();
        $league->teams->toArray();
      }

      // Fetch the division specified in the URL.
      if ($event->getRequest()->attributes->has('division')) {
        $divisionPath = $event->getRequest()->attributes->get('division');
        $controller->division = $controller->league->divisionByPath($divisionPath);
      }

      // Remember the controller for enhanced error handling.
      $this->controller = $controller;
    }
  }

  #[AsEventListener]
  public function onExceptionEvent(ExceptionEvent $event) {
    // Only intercept exceptions thrown by a league controller.
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
