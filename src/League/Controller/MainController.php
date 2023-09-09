<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Service\MatchDayService;
use Nsv\League\Api\Service\PlayerService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Api\Service\TeamService;
use Nsv\League\Entity\Round;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the main publicly accessible routes.
 */
#[Route('/ligen/{league}/', name: 'league_')]
class MainController extends AbstractLeagueController {

  const HOME_NEXT_DATES_COUNT = 2;
  const HOME_MAX_DATES_COUNT = 4;

  #[Route('overview/', name: 'overview')]
  public function overview(
    #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\d{4}-\d{2}-\d{2}$/'])]
    ?string $date,
    ScheduleService $service
  ): Response {
    $allDates = $service->leagueDates($this->league);
    if (!count($allDates)) {
      $this->addInfoMessage('Noch keine Spieltage hinterlegt.');
      return $this->renderWithLegacySystem('overview-no-dates.html.twig');
    }

    $today = date('Y-m-d');
    $dateToShow = $date ?: $service->closestDate($allDates, $today);
    $matches = $service->matchesByDate($this->league, $dateToShow);  // TODO: ...ForDate

    $hasMatches = false;
    foreach ($matches as $division) {
      if ($division->hasPairings()) {
        $hasMatches = true;
        break;
      }
    }
    
    return $this->renderWithLegacySystem('overview.html.twig', [
      'tabs' => $allDates,
      'activeTab' => $dateToShow,
      'matches' => $matches,
      'hasMatches' => $hasMatches
    ]);
  }

  #[Route('m/{teamId}/', name: 'team')]
  public function team(TeamService $service, int $teamId): Response {
    $teamEntity = $this->league->teamById($teamId);
    $team = $service->team($teamEntity);
    return $this->renderWithLegacySystem('team.html.twig', [
      'team' => $team,
      'teamEntity' => $teamEntity,
      'showContactInfo' => $this->league->year >= date('Y') - 1
    ]);
  }

  #[Route('api/teams/{teamId}/', name: 'api_team')]
  public function team_api(TeamService $service, int $teamId): Response {
    $teamEntity = $this->league->teamById($teamId);
    $team = $service->team($teamEntity);
    $team->captain->mail = '** REDACTED **';
    $team->captain->phone = '** REDACTED **';
    $team->captain->phone2 = '** REDACTED **';
    return $this->apiResponse($team);
  }

  #[Route('s/{playerId}/', name: 'player')]
  public function player(PlayerService $service, int $playerId): Response {
    $player = $service->player($this->league, $playerId);
    return $this->renderWithLegacySystem('player.html.twig', ['player' => $player]);
  }

  #[Route('s/{playerId}/debug/', name: 'player_debug')]
  public function player_debug(PlayerService $service, int $playerId): Response {
    $player = $service->player($this->league, $playerId);
    return $this->debugResponse($player);
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

  // TODO: round optional
  // TODO: requirements, otherwise matches schedule and stats
  #[Route('{division}/{round}/', name: 'matchday' /*, requirements: ['round' => '/\d+/'] */)]
  public function matchday(int $round, MatchDayService $service): Response {
    $matchDay = $this->matchday_internal($service, $round);
    return $this->renderWithLegacySystem('matchday/matchday.html.twig', [
      'matchDay' => $matchDay,
      'tabs' => $this->divisionTabs($round)
    ]);
  }
  
  #[Route('api/{division}/{round}/', name: 'api_matchday')]
  public function matchday_api(int $round, MatchDayService $service): Response {
    return $this->apiResponse($this->matchday_internal($service, $round));
  }

  private function matchday_internal(MatchDayService $service, int $round) {
    return $service->matchDay($this->division, $round, function() use ($round) {
      $this->initializeLegacySystem();
      $_GET['r'] = $round;
      require_once('tabelle.inc.php');
      return Tabelle($this->division->id, $round, true /* TODO: $kreuztabelle = false? */);
    });
  }

  /**
   * Returns the tab navigation configuration for division pages.
   */
  private function divisionTabs(mixed $active): array {
    $tabs = array_map(function(Round $round) use ($active) {
      return [
        'label' => 'R' . $round->round,
        'uri' => "../{$round->round}/",
        'active' => $active === $round->round
      ];
    }, $this->division->rounds());
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
 