<?php

namespace Tests\League;

use Doctrine\ORM\EntityNotFoundException;
use Nsv\League\Api\Service\RankingService;
use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Division;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\League\LeagueTestCase;
use Nsv\League\Api\Service\StatisticsService;
use PHPUnit\Framework\Attributes\DataProvider;

class StatisticsTest extends LeagueTestCase {
  use MatchesSnapshots;

  private StatisticsService $statisticsService;

  protected function setUp(): void {
    parent::setUp();
    $this->statisticsService = $this->container->get(StatisticsService::class);
  }

  /**
   * @return void
   */
  #[DataProvider('dwzCalculationProvider')]
  public function testRegulardwzCalculation($league, $division): void {
    $division = $this->division($league, $division);
    $teams_with_active_players = $this->statisticsService->teams_with_active_players($this->division);
    $active_teams_with_players = $this->statisticsService->active_teams_with_players($teams_with_active_players, $this->division);
    $dwzCalculationData = $this->statisticsService->teams_dwz_calculation($active_teams_with_players, $division);
    $this->assertMatchesSnapshot($dwzCalculationData);
  }

  public static function dwzCalculationProvider(): \Generator {
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