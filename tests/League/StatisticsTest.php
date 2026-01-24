<?php

namespace Tests\League;

use Doctrine\ORM\EntityNotFoundException;
use Nsv\League\Api\Service\RankingService;
use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Division;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\League\LeagueTestCase;
use Nsv\League\Api\Service\StatisticsService;

class StatisticsTest extends LeagueTestCase {
  use MatchesSnapshots;

  private StatisticsService $statisticsService;

  protected function setUp(): void {
    parent::setUp();
    $this->statisticsService = $this->container->get(StatisticsService::class);
  }

  public function testRegularStatistics(): void {
    $division = $this->division('bezirk1-1718', 'kreisliga-ost');
    $uppi = 3;
  }

  private function dwzCalculationProvider(): \Generator {
    yield 'Bezirk Hannover Kreisliga Ost 17/18' => ['bezirk1-1718', 'kreisliga-ost'];
    yield 'Bezirk Hannover Bezirksliga 18/19' => ['bezirk1-1718', 'kreisliga-ost'];
  }

  private function division(string $leaguePath, string $divisionPath): Division {
    $league = $this->leagueRepository->findByPathOrPrefix($leaguePath);
    return $league->divisionByPath($divisionPath);
  }
}