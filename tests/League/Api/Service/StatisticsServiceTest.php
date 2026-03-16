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

  /**
   * A team has had many forfeit games.
   */
  public function testTeamManyForfeitWins(): void {
    $division = $this->division('nsv-2223', 'verbandsliga-nord');
    $topscorerData = $this->statisticsService->team_game_score_data($this->statisticsService->active_teams_with_parings($division));
    $forfeitWinsCount = $topscorerData['7197']['forfeit_wins'];
    self::assertEquals(12, $forfeitWinsCount);
    $ulli = 'hulu';
    //https://nsv-online.local/ligen/nsv-2223/verbandsliga-nord/statistik
    // Post SV Uelzen 2 12mal kampflos
  }

  /**
   * A team has had many forfeit games.
   */
  public function testTeamManyForfeitLosses(): void {
    $division = $this->division('nsv-2223', 'verbandsliga-nord');
    $topscorerData = $this->statisticsService->team_game_score_data($this->statisticsService->active_teams_with_parings($division));
    $forfeitLossesCount = $topscorerData['7116']['forfeit_losses'];
    self::assertEquals(10, $forfeitLossesCount);
    $ulli = 'hulu';
    //https://nsv-online.local/ligen/nsv-2223/verbandsliga-nord/statistik
    // Post SV Uelzen 2 12mal kampflos
  }

  /**
   * A player has had multiple forfeit games.
   */
  public function testPlayerMultipleforfeitGames(): void {
    $division = $this->division('nsv-2223', 'verbandsliga-nord');
    // https://nsv-online.local/ligen/nsv-2223/s/518285/
    // https://nsv-online.local/ligen/nsv-2223/verbandsliga-nord/
    // Player Player 9a9a, 81d5fb5 2mal kampflos gewonnen
  }

  /**
   * A team has a low average age.
   */
  public function testTeamLowAverageAge(): void {
    $division = $this->division('nsv-2223', 'landesliga-nord');
    // https://nsv-online.local/ligen/nsv-2223/m/7177/
    // https://nsv-online.local/ligen/nsv-2223/landesliga-nord/statistik
    // SK Kirchweyhe 2 niedriges Durchschnittsalter 35 Jahre

  }

  /**
   * A team has a high average age.
   */
  public function testTeamHighAverageAge(): void {
    $division = $this->division('bezirk1-2324', 'kreisliga-ost');
    //https://nsv-online.local/ligen/bezirk1-2324/m/7534/
    // https://nsv-online.local/ligen/bezirk1-2324/kreisliga-ost/statistik
    // SK Anderten 2 hohes Durchschnitsalter 55 Jahre
  }

  /**
   * A team has a high win percentage.
   */
  public function testTeamHighWinPercentage(): void {
    $division = $this->division('bezirk1-2223', 'kreisliga-ost');
    //https://nsv-online.local/ligen/bezirk1-2223/m/7198/
    //https://nsv-online.local/ligen/bezirk1-2223/kreisliga-ost/statistik
    // SZ Bemerode hohe Siegquote mit Weiß 79%, mit Schwarz 74%

  }

  /**
   * A team has a low win percentage.
   */
  public function testTeamLowWinPercentage(): void {
    $division = $this->division('bezirk1-2223', 'kreisliga-ost');
    //https://nsv-online.local/ligen/bezirk1-2223/m/7163/
    // https://nsv-online.local/ligen/bezirk1-2223/kreisliga-ost/statistik

    // SV FB Wedemark 2 niedrige Siegquote Weiß 41%, Schwarz 31%
  }

  private function division(string $leaguePath, string $divisionPath): Division {
    $league = $this->leagueRepository->findByPathOrPrefix($leaguePath);
    return $league->divisionByPath($divisionPath);
  }
}