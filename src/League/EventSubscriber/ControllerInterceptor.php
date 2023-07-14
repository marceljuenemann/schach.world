<?php

namespace Nsv\League\EventSubscriber;

use Nsv\League\Controller\AbstractLeagueController;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Intercepts Controller calls for various magical things :)
 */
class ControllerInterceptor implements EventSubscriberInterface
{
  function __construct(private LeagueRepository $leagueRepository) {
  }

  public function onKernelController(ControllerEvent $event) {
    $controller = $event->getController();
    if (is_array($controller)) $controller = $controller[0];

    // Fetch the league specified in the URL.
    if ($controller instanceof AbstractLeagueController) {
      $leagueName = $event->getRequest()->attributes->get('league');
      $league = $this->leagueRepository->findByPath($leagueName);
      if (!$league) {
        // TODO: Make this lead to a 404
        throw new \Exception("No tournament with given path found");
      }
      $controller->setLeague($league);
    }
  }

  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::CONTROLLER => 'onKernelController'
    ];
  }
}
