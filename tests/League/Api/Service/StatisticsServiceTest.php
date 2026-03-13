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

  public static function divisionDataProvider(): \Generator {
    yield 'Bezirk Hannover Kreisliga Ost 17/18' => ['bezirk1-1718', 'kreisliga-ost'];
    yield 'Bezirk Hannover Bezirksliga 18/19' => ['bezirk1-1819', 'bezirksliga'];
    yield 'Bezirk 3 Bezirksklasse 21/22' => ['bezirk3-2122', 'bezirksklasse'];
    yield 'Landesliga Süd 21/22' => ['nsv-2122', 'landesliga-sued'];
    yield 'Verbandsliga Nord 22/23' => ['nsv-2223', 'verbandsliga-nord'];
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

  /**
   * No matches have been played.
   * calculate_topscorer() should return an empty array.
   */
  public function testNoMatches(): void {
    $division = $this->division('sjbh-2021', 'bmm-u14');
    self::assertEquals([], $this->statisticsService->calculate_topscorer($division));
  }

  /**
   * There is only one Topscorer
   */
  public function testOneTopScorer(): void {
    $division = $this->division('bezirk1-1718', 'kreisliga-ost');
    $topscorerData = $this->statisticsService->calculate_topscorer($division);
    $countTopScorers = count($topscorerData['text_values']['text_top_scorers']);

    self::assertEquals(1, $countTopScorers);
  }

  /**
   * There are multiple Topscorers
   */
  public function testMultipleTopScorers(): void {
    $division = $this->division('nsv-2223', 'landesliga-nord');
    $topscorerData = $this->statisticsService->calculate_topscorer($division);
    $countTopScorers = count($topscorerData['text_values']['text_top_scorers']);

    self::assertGreaterThan(1, $countTopScorers);
  }

  /**
   * There is only one Draw King
   */
  public function testOneDrawKing(): void {
    $division = $this->division('bezirk3-2122', 'bezirksklasse');
    $topscorerData = $this->statisticsService->calculate_topscorer($division);
    $countDrawKings = count($topscorerData['text_values']['text_draw_kings']);

    self::assertEquals(1, $countDrawKings);

  }

  /**
   * There are multiple Draw Kings
   */
  public function testMultipleDrawKings(): void {
    $division = $this->division('nsv-2122', 'landesliga-sued');
    $topscorerData = $this->statisticsService->calculate_topscorer($division);
    $countDrawKings = count($topscorerData['text_values']['text_draw_kings']);

    self::assertGreaterThan(1, $countDrawKings);

  }

  private function division(string $leaguePath, string $divisionPath): Division {
    $league = $this->leagueRepository->findByPathOrPrefix($leaguePath);
    return $league->divisionByPath($divisionPath);
  }
}