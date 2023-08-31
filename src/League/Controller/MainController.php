<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Service\MatchDayService;
use Nsv\League\Api\Service\PlayerService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Api\Service\TeamService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the main publicly accessible routes.
 * 
 * TODO: Merge with Division and Team controller
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
    $matches = $service->matchesByDate($this->league, $dateToShow);

    // Show at most three future dates and at most five tabs in total.
    $pos = array_search($dateToShow, $allDates);
    $tabs = array_slice($allDates, 0, $pos + 1 + self::HOME_NEXT_DATES_COUNT);
    $tabs = array_slice($tabs, max(count($tabs) - self::HOME_MAX_DATES_COUNT, 0));

    $hasMatches = false;
    foreach ($matches as $division) {
      if ($division->hasPairings()) {
        $hasMatches = true;
        break;
      }
    }
    
    return $this->renderWithLegacySystem('overview.html.twig', [
      'tabs' => $tabs,
      'activeTab' => $dateToShow,
      'matches' => $matches,
      'hasMatches' => $hasMatches
    ]);
  }

  #[Route('m/{teamId}/', name: 'team')]
  public function team(TeamService $service, int $teamId): Response {
    $team = $service->team($this->league, $teamId);
    $showContactInfo = $this->league->year >= date('Y') - 1;
    return $this->renderWithLegacySystem('team.html.twig', [
      'team' => $team,
      'showContactInfo' => $showContactInfo
    ]);
  }

  #[Route('api/teams/{teamId}/', name: 'api_team')]
  public function team_api(TeamService $service, int $teamId): Response {
    $team = $service->team($this->league, $teamId);
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
    return $this->renderWithLegacySystem('schedule.html.twig', ['matchDays' => $matchDays]);
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
    $matchDay = $service->matchDay($this->division, $round);
    return $this->renderWithLegacySystem('matchday.html.twig', ['matchDay' => $matchDay]);
  }
  
  // TODO: round optional
  #[Route('{division}/{round}/debug', name: 'matchday_debug' /*, requirements: ['round' => '/\d+/'] */)]
  public function matchday_debug(int $round, MatchDayService $service): Response {
    $matchDay = $service->matchDay($this->division, $round);
    return $this->debugResponse($matchDay);
  }
}
