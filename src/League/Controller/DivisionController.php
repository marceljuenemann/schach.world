<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Service\MatchDayService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Core\Encoding;
use Nsv\League\Core\LeagueAuthState;
use Nsv\League\Core\LegacySystem;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Round;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nsv\League\Api\Service\StatisticsService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Controller for division specific routes.
 */
#[Route('/ligen/{league}/', name: 'league_division_', priority: -100)]
class DivisionController extends AbstractLeagueController
{

  private $entityManager;

  function __construct(
    League $league,
    LeagueAuthState $auth,
    LegacySystem $legacySystem,
    Division $division,
    private EntityManagerInterface $leagueEntityManager
  ) {
    parent::__construct($league, $auth, $legacySystem);
    $this->division = $division;
    $this->entityManager = $this->leagueEntityManager;
  }

  #[Route('{division}/spielplan/', name: 'schedule')]
  public function schedule(ScheduleService $service): Response
  {
    $matchDays = $service->divisionSchedule($this->division);
    return $this->renderWithLegacySystem('schedule.html.twig', [
      'matchDays' => $matchDays,
      'tabs' => $this->divisionTabs('schedule')
    ]);
  }

  #[Route('{division}/spielplan/debug/', name: 'schedule_debug')]
  public function schedule_debug(ScheduleService $service): Response
  {
    $matchDays = $service->divisionSchedule($this->division);
    return $this->debugResponse($matchDays);
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

  #[Route('api/divisions/{division}/rounds/{round}/', name: 'api_matchday')]
  public function matchday_api(int $round, MatchDayService $service): Response
  {
    return $this->apiResponse($this->matchday_model($service, $round));
  }

  #[Route('{division}/statistik', name: 'statistik')]
  public function statistics(StatisticsService $service): Response
  {

       $division_name = $this->division->name;

    $dwz_data = $service->create_dwz_statistics_table($this->division);

    $dwz_table = $dwz_data['table'];

    $topscorer_data = $service->create_topscorer_table($this->division);

    $topscorer_table = $topscorer_data['table'];

    $team_game_score_data = $service->create_team_game_score_table($this->division);

    $team_game_score_table = $team_game_score_data['table'];

    $intro_text_values = array_merge($dwz_data['text_values'], $topscorer_data['text_values'], $team_game_score_data['text_values']);


    return $this->renderWithLegacySystem('division/statistics.html.twig',
      [
        'division_name' => $division_name,
        'intro_text_values' => $intro_text_values,
        'dwz_table' => $dwz_table,
        'topscorer_table' => $topscorer_table,
        'team_game_score_table' => $team_game_score_table,
      ]);

  }

  #[Route('{division}/{round}/', name: 'matchday')]
  public function matchday(int $round, MatchDayService $service): Response
  {
    $matchDay = $this->matchday_model($service, $round);
    return $this->renderWithLegacySystem('matchday/matchday.html.twig', [
      'matchDay' => $matchDay,
      'tabs' => $this->divisionTabs()
    ]);
  }

  private function matchday_model(MatchDayService $service, int $round)
  {
    return $service->matchDayCached($this->division, $round, function () use ($round) {
      $this->initializeLegacySystem();
      $_GET['r'] = $round;
      require_once('tabelle.inc.php');
      return Tabelle($this->division->id, $round, true /* TODO: $kreuztabelle = false? */);
    });
  }

  #[Route('{division}/', name: 'index')]
  public function index(ScheduleService $scheduleService, MatchDayService $matchDayService): Response
  {
    $round = $scheduleService->closestRound($this->division, date('Y-m-d'));
    return $this->matchday($round ? $round->round : 1, $matchDayService);
  }



  /**
   * Returns the tab navigation configuration for division pages.
   */
  // TODO: Might no longer need this?
  private function divisionTabs(string $active = null): array
  {
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
