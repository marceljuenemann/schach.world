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

    $this->assertNull($this->service->closestRound($division, '2025-01-01'));
  }

  public function testClosestRound_futureDate_returnsFirst() {
    // Round 3 and 4 are both set to 2025-03-03 in LeagueFixture.
    $round = $this->service->closestRound($this->division, '2025-03-01');
    $this->assertEquals('2025-03-03', $round->date);
    $this->assertEquals(3, $round->round);
  }

  public function testClosestRound_sameDate_returnsFirst() {
    // Round 3 and 4 are both set to 2025-03-03 in LeagueFixture.
    $round = $this->service->closestRound($this->division, '2025-03-01');
    $this->assertEquals('2025-03-03', $round->date);
    $this->assertEquals(3, $round->round);
  }

  public function testClosestRound_pastDate_returnsLast() {
    // Round 3 and 4 are both set to 2025-03-03 in LeagueFixture.
    $round = $this->service->closestRound($this->division, '2025-04-01');
    $this->assertEquals('2025-03-03', $round->date);
    $this->assertEquals(4, $round->round);
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
