<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Service\MatchDayService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Core\LeagueAuthState;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Round;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for division specific routes.
 */
#[Route('/ligen/{league}/', name: 'league_division_', priority: -100)]
class DivisionController extends AbstractLeagueController {

  function __construct(
    League $league,
    LeagueAuthState $auth,
    Division $division
  ) {
    parent::__construct($league, $auth);
    $this->division = $division;
  }

  #[Route('{division}/spielplan/', name: 'schedule')]
  public function schedule(ScheduleService $service): Response {
    $matchDays = $service->matchDays($this->division);
    return $this->renderWithLegacySystem('schedule.html.twig', [
      'matchDays' => $matchDays,
      'tabs' => $this->divisionTabs('schedule')
    ]);
  }

  #[Route('{division}/spielplan/debug/', name: 'schedule_debug')]
  public function schedule_debug(ScheduleService $service): Response {
    $matchDays = $service->matchDays($this->division);
    return $this->debugResponse($matchDays);
  }

  #[Route('api/divisions/{division}/rounds/{round}/', name: 'api_matchday')]
  public function matchday_api(int $round, MatchDayService $service): Response {
    return $this->apiResponse($this->matchday_model($service, $round));
  }

  #[Route('{division}/{round}/', name: 'matchday')]
  public function matchday(int $round, MatchDayService $service): Response {
    $matchDay = $this->matchday_model($service, $round);
    return $this->renderWithLegacySystem('matchday/matchday.html.twig', [
      'matchDay' => $matchDay,
      'tabs' => $this->divisionTabs($round)
    ]);
  }
  
  private function matchday_model(MatchDayService $service, int $round) {
    return $service->matchDay($this->division, $round, function() use ($round) {
      $this->initializeLegacySystem();
      $_GET['r'] = $round;
      require_once('tabelle.inc.php');
      return Tabelle($this->division->id, $round, true /* TODO: $kreuztabelle = false? */);
    });
  }

  #[Route('{division}/', name: 'index')]
  public function index(ScheduleService $scheduleService, MatchDayService $matchDayService): Response {
    $round = $scheduleService->closestRound($this->division, date('Y-m-d'));
    return $this->matchday($round ? $round->round : 1, $matchDayService);
  }

  /**
   * Returns the tab navigation configuration for division pages.
   */
  private function divisionTabs(mixed $active): array {
    $tabs = array_map(function(Round $round) use ($active) {
      return [
        'label' => 'R' . $round->round,
        'uri' => "../{$round->round}/",  // TODO: Use URI once launched.
        'active' => $active === $round->round
      ];
    }, $this->division->roundsWithDate());
    $tabs [] = [
      'label' => 'Spielplan',
      'uri' => $this->division->scheduleUri(),
      'active' => $active === 'schedule'
    ];
    $tabs[] = [
      'label' => 'Statistik',
      'uri' => $this->division->statsUri(),
      'active' => $active === 'stats'
    ];
    return $tabs;
  }
}
