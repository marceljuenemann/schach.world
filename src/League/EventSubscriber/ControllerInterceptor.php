<?php

namespace Nsv\League\EventSubscriber;

use Nsv\League\Controller\AbstractLeagueController;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Intercepts Controller calls for various magical things :)
 */
// TODO: Move to Core/
class ControllerInterceptor implements EventSubscriberInterface
{
  function __construct(private LeagueRepository $leagueRepository) {
  }

  public function onKernelController(ControllerEvent $event) {
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
      }

      // Fetch the division specified in the URL.
      if ($event->getRequest()->attributes->has('division')) {
        $divisionPath = $event->getRequest()->attributes->get('division');
        $controller->division = $controller->league->divisionByPath($divisionPath);
      }
    }
  }

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::CONTROLLER => 'onKernelController'
    ];
  }
}
