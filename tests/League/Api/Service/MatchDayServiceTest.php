<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Tests\League\LeagueTestCase;

class MatchDayServiceTest extends LeagueTestCase
{
  private MatchDayService $service;
  private League $league;
  private Division $division;

  protected function setUp(): void {
    parent::setUp();
    $this->service = $this->container->get(MatchDayService::class);
    $this->league = $this->leagueRepository->findByPathOrPrefix('nsv-2526');
    $this->division = $this->league->divisions[0];
  }

  // TODO: Test lastModified field manually.
  
  public function testMatchDay_round1() {
    $this->testMatchDay(1);
  }

  public function testMatchDay_round2() {
    $this->testMatchDay(2);
  }

  public function testMatchDay_round3() {
    $this->testMatchDay(3);
  }

  public function testMatchDay_round4() {
    $this->testMatchDay(4);
  }

  public function testMatchDay_round5() {
    $this->testMatchDay(5);
  }

  private function testMatchDay(int $round) {
    $model = $this->service->matchDay($this->division, $round);
    $this->assertMatchesSnapshot($model);
  } 
}
