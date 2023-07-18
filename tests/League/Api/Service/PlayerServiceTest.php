<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Entity\League;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlayerServiceTest extends KernelTestCase
{
  var PlayerService $service;
  var League $league;

  protected function setUp(): void {
    $container = static::getContainer();
    $this->service = $container->get(PlayerService::class);
    $this->league = $container->get(LeagueRepository::class)->find(1);
  }

  public function testSomething() {
    $this->assertEquals('Test League', $this->league->name);

  }
}
