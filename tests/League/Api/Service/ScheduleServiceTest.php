<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Entity\League;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ScheduleServiceTest extends KernelTestCase
{
  var ScheduleService $service;
  var League $league;

  protected function setUp(): void {
    $container = static::getContainer();
    $this->service = $container->get(ScheduleService::class);
    $this->league = $container->get(LeagueRepository::class)->findByPath('test'); // TODO: Fixture?
  }

  /**
   * TODO:
   * Add cases for
   * - custom date
   *  - unknown date
   * - round with date, but without games
   * - round without date
   * - staffel date
   * - turnier date
   * - round dates swapped (COVID case)
   * - custom host
   * - with result
   * - without result
   * 
   * 
   */

  public function testMatchDays() {
    $division = $this->league->divisions[0];
    $matchDays = $this->service->matchDays($division);

    // TODO: move to JSON?
    $expected = file_get_contents(str_replace('.php', '.txt', __FILE__));
    $this->assertEquals($expected, print_r($matchDays, true)); 
  }
}
