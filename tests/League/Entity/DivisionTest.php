<?php

namespace Nsv\League\Api\Service;

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

    $this->division = new Division();
    $this->division->id = 42;
    $this->division->league = $this->league;
  }

  public function testMatchDayUri() {
    $this->assertEquals("/ligen/test-league/?staffel=42&r=", $this->division->matchDayUri());
    $this->assertEquals("/ligen/test-league/?staffel=42&r=2", $this->division->matchDayUri(2));
  }
}
