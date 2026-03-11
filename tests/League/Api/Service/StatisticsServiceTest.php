<?php

namespace League\Api\Service;

use Nsv\League\Api\Service\StatisticsService;
use Nsv\League\Entity\Division;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\League\LeagueTestCase;

class StatisticsServiceTest extends LeagueTestCase {
  use MatchesSnapshots;

  private StatisticsService $statisticsService;

  protected function setUp(): void {
    parent::setUp();
    $this->statisticsService = $this->container->get(StatisticsService::class);
  }

  /**
   * Snapshot-Test for first data method for the 'DWZ-Statistik' table.
   * Testing regular divisions with no special cases.
   */
  #[DataProvider('divisionDataProvider')]
  public function testTeamsDwzCalculation($league, $division): void {
    $division = $this->division($league, $division);
    $teams_with_active_players = $this->statisticsService->teams_with_active_players($division);
    $active_teams_with_players = $this->statisticsService->active_teams_with_players($teams_with_active_players, $division);
    $dwzTeamsCalculationData = $this->statisticsService->teams_dwz_calculation($active_teams_with_players, $division);
    $this->assertMatchesSnapshot($dwzTeamsCalculationData);
  }

  /**
   * Snapshot-Test for second data method for the 'DWZ-Statistik' table.
   * Testing regular divisions with no special cases.
   */
  #[DataProvider('divisionDataProvider')]
  public function testDwzStatisticsAdditionalData($league, $division): void {
    $division = $this->division($league, $division);
    $teams_with_active_players = $this->statisticsService->teams_with_active_players($division);
    $active_teams_with_players = $this->statisticsService->active_teams_with_players($teams_with_active_players, $division);
    $dwzStatisticsAdditionalData = $this->statisticsService->dwz_statistics_additional_data($active_teams_with_players, $division);
    $this->assertMatchesSnapshot($dwzStatisticsAdditionalData);
  }

  /**
   * Snapshot-Test for data method for the 'Topscorer' table.
   * Testing regular divisions with no special cases.
   */
  #[DataProvider('divisionDataProvider')]
  public function testCalculateTopscorer($league, $division): void {
    $division = $this->division($league, $division);
    $calculateTopscorerData = $this->statisticsService->calculate_topscorer($division);
    $this->assertMatchesSnapshot($calculateTopscorerData);
  }

  /**
   * Snapshot-Test for first data method for the 'Spiel-Statistik' table.
   * Testing regular divisions with no special cases.
   */
  #[DataProvider('divisionDataProvider')]
  public function testTeamGameScoreData($league, $division): void {
    $division = $this->division($league, $division);
    $active_teams_with_parings = $this->statisticsService->active_teams_with_parings($division);
    $teamGameScoreData = $this->statisticsService->team_game_score_data($active_teams_with_parings);
    $this->assertMatchesSnapshot($teamGameScoreData);
  }

  /**
   * Snapshot-Test for second data method for the 'Spiel-Statistik' table.
   * Testing regular divisions with no special cases.
   */
  #[DataProvider('divisionDataProvider')]
  public function testTeamGameScoreAdditionalData($league, $division): void {
    $division = $this->division($league, $division);
    $teamGameScoreAdditionalData = $this->statisticsService->team_game_score_additional_data($division);
    $this->assertMatchesSnapshot($teamGameScoreAdditionalData);
  }

  public static function divisionDataProvider(): \Generator {
    yield 'Bezirk Hannover Kreisliga Ost 17/18' => ['bezirk1-1718', 'kreisliga-ost'];
    yield 'Bezirk Hannover Bezirksliga 18/19' => ['bezirk1-1819', 'bezirksliga'];
    yield 'Bezirk 3 Bezirksklasse 21/22' => ['bezirk3-2122', 'bezirksklasse'];
    yield 'Landesliga Süd 21/22' => ['nsv-2122', 'landesliga-sued'];
    yield 'Verbandsliga Nord 22/23' => ['nsv-2223', 'verbandsliga-nord'];
  }

  private function division(string $leaguePath, string $divisionPath): Division {
    $league = $this->leagueRepository->findByPathOrPrefix($leaguePath);
    return $league->divisionByPath($divisionPath);
  }
}