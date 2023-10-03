<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;

class ScheduleServiceTest extends AbstractApiTest
{
  private ScheduleService $service;

  protected function setUp(): void {
    parent::setUp();
    $this->service = $this->container->get(ScheduleService::class);
  }

  public function testClosestDate() {
    $dates = ['2020-01-01', '2020-01-04'];
    $this->assertEquals('2020-01-04', $this->service->closestDate($dates, '2020-01-03'));
  }

  public function testClosestDate_exactDate() {
    $dates = ['2020-01-01', '2020-01-04', '2020-01-06'];
    $this->assertEquals('2020-01-04', $this->service->closestDate($dates, '2020-01-04'));
  }

  public function testClosestDate_noDates() {
    $this->assertNull($this->service->closestDate([], '2020-01-04'));
  }

  public function testClosestRound_noDates_returnsNull() {
    $league = new League();
    $league->dates = [];
    $division = new Division();
    $division->league = $league;
    $division->pairings = [];

    $this->assertNull($this->service->closestRound($division, '2025-01-01'));
  }

  public function testClosestRound_futureDate_returnsFirst() {
    // Round 2 and 4 are both set to 2025-02-02 in LeagueFixture.
    $round = $this->service->closestRound($this->division, '2024-02-01');
    $this->assertEquals('2024-02-02', $round->date);
    $this->assertEquals(2, $round->round);
  }

  public function testClosestRound_sameDate_returnsFirst() {
    // Round 2 and 4 are both set to 2025-02-02 in LeagueFixture.
    $round = $this->service->closestRound($this->division, '2024-02-02');
    $this->assertEquals('2024-02-02', $round->date);
    $this->assertEquals(2, $round->round);
  }

  public function testClosestRound_pastDate_returnsLast() {
    // Round 2 and 4 are both set to 2025-02-02 in LeagueFixture.
    $round = $this->service->closestRound($this->division, '2024-02-03');
    $this->assertEquals('2024-02-02', $round->date);
    $this->assertEquals(4, $round->round);
  }

  public function testClosestRound_roundWithNoPairing_roundIgnored() {
    // Round 3 is scheduled on 2025-03-03, but has no pairings.
    $round = $this->service->closestRound($this->division, '2025-03-03');
    $this->assertEquals(1, $round->round);
    $this->assertEquals('2025-01-01', $round->date);
  }

  public function testClosestRound_noDates_noRoundReturned() {
    $this->league->dates = [];
    $this->assertNull($this->service->closestRound($this->division, '2025-01-01'));
  }

  public function testMatchesByDate() {
    $matchDays = $this->service->matchesByDate($this->league, '2025-01-01');
    $this->assertModel($matchDays, __FILE__, __FUNCTION__);
  }

  public function testDivisionSchedule() {
    $matchDays = $this->service->divisionSchedule($this->division);
    $this->assertModel($matchDays, __FILE__, __FUNCTION__);
  }

}
