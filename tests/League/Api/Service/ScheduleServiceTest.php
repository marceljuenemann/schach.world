<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Core\Encoding;
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

  public function testMatchDays() {
    $division = $this->league->divisions[0];
    $matchDays = $this->service->matchDays($division);

    // TODO: ignore IDs being different. Maybe write actual tests rather than just comparing output :D
    // TODO: move to base class.
    // TODO: move to JSON?
    $actual = print_r($matchDays, true);
    $path = str_replace('.php', '.txt', __FILE__);
    $expectedPath = str_replace('/Api/Service/', '/Api/Service/expected/',  $path);
    $actualPath = str_replace('/Api/Service/', '/Api/Service/actual/',  $path);
    $expected = Encoding::utf8_decode(file_get_contents($expectedPath));
    file_put_contents($actualPath, Encoding::utf8_encode($actual));
    $this->assertEquals($expected, $actual); 
  }
}
