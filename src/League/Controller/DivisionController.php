<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Service\MatchDayService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Core\Encoding;
use Nsv\League\Core\LeagueAuthState;
use Nsv\League\Core\LegacySystem;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nsv\League\Api\Service\StatisticsService;
use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Api\Service\PgnService;

/**
 * Controller for division specific routes.
 */
#[Route('/ligen/{league}/', name: 'league_division_', priority: -100)]
class DivisionController extends AbstractLeagueController {

  function __construct(
    League                         $league,
    LeagueAuthState                $auth,
    LegacySystem                   $legacySystem,
    Division                       $division) {
    parent::__construct($league, $auth, $legacySystem);
    $this->division = $division;
  }

  #[Route('{division}/spielplan/', name: 'schedule')]
  public function schedule(ScheduleService $service): Response {
    $matchDays = $service->divisionSchedule($this->division);
    return $this->renderWithLegacySystem('schedule.html.twig', [
      'matchDays' => $matchDays,
      'tabs' => $this->divisionTabs('schedule')
    ]);
  }

  #[Route('{division}/spielplan/debug/', name: 'schedule_debug')]
  public function schedule_debug(ScheduleService $service): Response {
    $matchDays = $service->divisionSchedule($this->division);
    return $this->debugResponse($matchDays);
  }

  #[Route('{division}/statistik', 'statistik')]
  public function statistics(StatisticsService $service): Response {

    $division_name = $this->division->name;

    $teams_with_active_players = $service->teams_with_active_players($this->division);
    $active_teams_with_players = $service->active_teams_with_players($teams_with_active_players, $this->division);
    $active_teams_with_parings = $service->active_teams_with_parings($this->division);
    $dwzData = [];

    // Check if any games have been played. Some leagues have been
    // created, but no games were ever played and entered into the system.
    if (!empty($service->all_games_division($this->division))
      && !empty($service->calculate_topscorer($this->division))
      && !empty($active_teams_with_players)) {
      $dwzData = $service->teams_dwz_calculation($active_teams_with_players, $this->division);
      $dwzAdditionalData = $service->dwz_statistics_additional_data($active_teams_with_players, $this->division);

      $teamGameScoreData = $service->team_game_score_data($active_teams_with_parings);
      $teamGameScoreAdditionalData = $service->team_game_score_additional_data($this->division);

      $introTextValues['dwzTextValues'] = $dwzAdditionalData['dwzTextValues'];


      return $this->renderWithLegacySystem('division/statistics.html.twig',
        [
          'division_name' => $division_name,
          'introTextValues' => $introTextValues,
          'dwzData' => $dwzData,
          'dwzAdditionalData' => $dwzAdditionalData,
          'topScorerData' => $service->calculate_topscorer($this->division),
          'teamGameScoreData' => $teamGameScoreData,
          'teamGameScoreAdditionalData' => $teamGameScoreAdditionalData,
          'tabs' => $this->divisionTabs('stats')
        ]);
    }


  else {
      // If no games have been played, just return the Division title.
      // This relies on "strict_variables: false" in twig.yaml. Else
      // the template would create errors due to missing content variables.
      return $this->renderWithLegacySystem('division/statistics.html.twig',
        [
          'division_name' => $division_name,
          'tabs' => $this->divisionTabs('stats')
        ]);
    }
  }

  #[Route('{division}/{round}/pdf/', name: 'pdf')]
  public function pdf(int $round): Response {
    $this->initializeLegacySystem();
    $_GET['r'] = $round;
    $_GET['ausgabe'] = 'pdf';

    ob_start();
    require('../_module/spieltag/spieltag.php');
    $body = ob_get_clean();
    $response = new Response($body);
    $response->setCharset(Encoding::CHARSET);
    return $response;
  }

  #[Route('{division}/{round}/pgn/', name: 'pgn')]
  public function pgn(PgnService $pgnService, int $round): Response {
    $response = new Response($pgnService->renderPgn($this->division, $this->division->round($round)));
    $filename = $this->division->path() . '-R' . $round . '.pgn';
    $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
    $response->headers->set('Content-Type', 'application/x-chess-pgn; charset=' . Encoding::CHARSET_UTF8);
    return $response;
  }

  #[Route('api/divisions/{division}/rounds/current/', name: 'api_current_matchday')]
  public function current_matchday_api(ScheduleService $scheduleService, MatchDayService $service): Response {
    $round = $scheduleService->closestRound($this->division, date('Y-m-d'));
    return $this->apiResponse($this->matchday_model($service, $round ? $round->round : 1));
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
      'tabs' => $this->divisionTabs()
    ]);
  }

  private function matchday_model(MatchDayService $service, int $round) {
    return $service->matchDay($this->division, $round);
  }

  #[Route('{division}/', name: 'index')]
  public function index(ScheduleService $scheduleService, MatchDayService $matchDayService): Response {
    $round = $scheduleService->closestRound($this->division, date('Y-m-d'));
    return $this->matchday($round ? $round->round : 1, $matchDayService);
  }

  /**
   * Returns the tab navigation configuration for division pages.
   */
  // TODO: Might no longer need this?
  private function divisionTabs(string | null $active = null): array {
    $tabs [] = [
      'label' => 'Spieltage',
      'uri' => $this->league->uri() . $this->division->path() . '/',  // TODO: use uri()
      'active' => $active === null
    ];
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
