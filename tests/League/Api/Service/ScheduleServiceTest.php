<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Tests\League\LeagueTestCase;

class ScheduleServiceTest extends LeagueTestCase
{
  private ScheduleService $service;
  private League $league;
  private Division $division;

  protected function setUp(): void {
    parent::setUp();
    $this->service = $this->container->get(ScheduleService::class);
    $this->league = $this->leagueRepository->findByPathOrPrefix('nsj-1819');
    $this->division = $this->league->divisionByPath('landesklasse-sued');
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
    // Round 5, 6 and 7 are all scheduled for 2018-12-02.
    $round = $this->service->closestRound($this->division, '2018-11-20');
    $this->assertEquals('2018-12-02', $round->date);
    $this->assertEquals(5, $round->round);
  }

  public function testClosestRound_sameDate_returnsFirst() {
    // Round 5, 6 and 7 are all scheduled for 2018-12-02.
    $round = $this->service->closestRound($this->division, '2018-12-02');
    $this->assertEquals('2018-12-02', $round->date);
    $this->assertEquals(5, $round->round);
  }

  public function testClosestRound_pastDate_returnsLast() {
    // Round 5, 6 and 7 are all scheduled for 2018-12-02.
    $round = $this->service->closestRound($this->division, '2018-12-03');
    $this->assertEquals('2018-12-02', $round->date);
    $this->assertEquals(7, $round->round);
  }

  public function testClosestRound_roundWithNoPairing_roundIgnored() {
    // Round 7 is scheduled on 2019-05-05, but has no pairings.
    $division = $this->league->divisionByPath('aufstiegsspiele');
    $round = $this->service->closestRound($division, '2019-05-17');
    $this->assertEquals(3, $round->round);
    $this->assertEquals('2019-05-04', $round->date);
  }

  public function testClosestRound_noDates_noRoundReturned() {
    $this->league->dates = [];
    $this->assertNull($this->service->closestRound($this->division, '2025-01-01'));
  }

  public function testMatchesByDate() {
    $matchDays = $this->service->matchesByDate($this->league, '2025-01-01');
    $this->assertMatchesSnapshot($matchDays);
  }
  
  public function testDivisionSchedule() {
    $matchDays = $this->service->divisionSchedule($this->division);
    $this->assertMatchesSnapshot($matchDays);
  }

}
