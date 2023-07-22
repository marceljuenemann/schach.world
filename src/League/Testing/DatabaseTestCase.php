<?php

namespace Nsv\League\Testing;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Repository\DivisionRepository;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * Abstract test case that runs against an actual database and helps with
 * creating test data on-the-fly. This test does not make use of the LeagueFixtures.
 */
abstract class DatabaseTestCase extends KernelTestCase
{
  protected Container $container;
  protected LeagueRepository $leagueRepository;
  protected DivisionRepository $divisionRepository;

  protected League $league;
  protected Division $division1;
  protected Division $division2;

  protected function setUp(): void {
    $this->container = static::getContainer();
    $this->leagueRepository = $this->container->get(LeagueRepository::class);
    $this->divisionRepository = $this->container->get(DivisionRepository::class);

    $this->league = $this->addLeague();
    $this->division1 = $this->addDivision("Division A");
    $this->division2 = $this->addDivision("Division B");

    $this->leagueRepository->getEntityManager()->flush();
  }

  protected function addLeague(): League {
    $league = new League();
    $league->name = "LeagueTestCase";
    $league->path = "test-" . substr(md5(rand()), 0, 7);
    $league->organisation = '7';
    $this->leagueRepository->persist($league);
    return $league;
  }

  protected function addDivision(string $name): Division {
    $division = new Division();
    $division->league = $this->league;
    $division->name = $name;
    $division->sortId = 1;
    $this->divisionRepository->persist($division);
    return $division;
  }
}
