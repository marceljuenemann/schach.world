<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Core\LeagueAuth;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for division specific routes.
 */
#[Route('/ligen/{league}/', name: 'league_division_')]
class DivisionController extends AbstractLeagueController {

  function __construct(
    League $league,
    LeagueAuth $auth,
    Division $division
  ) {
    parent::__construct($league, $auth);
    $this->division = $division;
  }

  #[Route('{division}/spielplan/', name: 'schedule')]
  public function schedule(ScheduleService $service): Response {
    $matchDays = $service->matchDays($this->division);
    return $this->renderWithLegacySystem('schedule.html.twig', ['matchDays' => $matchDays]);
  }

  #[Route('{division}/spielplan/debug/', name: 'schedule_debug')]
  public function schedule_debug(ScheduleService $service): Response {
    $matchDays = $service->matchDays($this->division);
    return $this->debugResponse($matchDays);
  }
}
