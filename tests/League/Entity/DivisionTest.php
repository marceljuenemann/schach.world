<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Entity\Date;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use PHPUnit\Framework\TestCase;

class DivisionTest extends TestCase
{
  private League $league;
  private Division $division;

  protected function setUp(): void {
    $this->league = new League();
    $this->league->path = 'test-league';
    $this->league->dates = [];

    $this->division = new Division();
    $this->division->id = 42;
    $this->division->league = $this->league;
  }

  public function testUri() {
    $this->assertEquals("/ligen/test-league/?staffel=42&r=", $this->division->uri());
    $this->assertEquals("/ligen/test-league/?staffel=42&r=2", $this->division->round(2)->uri());
  }

  public function testDates() {
    $date1 = $this->addDate(1, '2020-01-01');
    $fake1 = $this->addDate(2, '2020-01-01', new Division());  // not for this division.
    $date2 = $this->addDate(2, '2020-02-02', $this->division);
    $fake2 = $this->addDate(2, '2020-02-03');

    $this->assertEquals([1 => $date1, 2 => $date2], $this->division->dates());
  }

  public function testRoundsWithDate() {
    $date2 = $this->addDate(2, '2020-01-01');
    $date1 = $this->addDate(1, '2020-02-02');
    $date5 = $this->addDate(5, '2020-03-03');
    $this->division->configRounds = 3;  // Only 3 rounds

    $rounds = $this->division->roundsWithDate();
    $this->assertEquals([2, 1], array_keys($rounds));
    $this->assertEquals('2020-01-01', $rounds[2]->date);
    $this->assertEquals('2020-02-02', $rounds[1]->date);
  }

  private function addDate($round, $date, $division = null) {
    $entity = new Date();
    $entity->league = $this->league;
    $entity->round = $round;
    $entity->date = $date;
    $entity->division = $division;

    $this->league->dates = array_merge($this->league->dates, [$entity]);
    return $entity;
  }
}
