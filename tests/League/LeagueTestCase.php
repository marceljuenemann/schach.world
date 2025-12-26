<?php

namespace Tests\League;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Repository\LeagueRepository;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class LeagueTestCase extends KernelTestCase
{
  use MatchesSnapshots;

  protected Container $container;
  protected EntityManagerInterface $em;
  protected LeagueRepository $leagueRepository;

  protected function setUp(): void {
    $this->container = static::getContainer();
    $this->em = $this->container->get(LeagueRepository::class)->getEntityManager();
    $this->leagueRepository = $this->container->get(LeagueRepository::class);
  }

  protected function clear(): void {
    $this->em->clear();
    $this->setUp();
  }
}
