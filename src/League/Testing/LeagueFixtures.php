<?php

namespace Nsv\League\Testing;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;

/**
 * Creates test data for the league manager.
 */
class LeagueFixtures extends Fixture implements ORMFixtureInterface
{
  public function load(ObjectManager $manager): void {
    $league = new League();
    $league->name = "Test League";
    $league->path = "test";
    $manager->persist($league);

    $division = new Division();
    $division->league = $league;
    $division->name = "Test Division";
    $division->sortId = 1;
    $manager->persist($division);

    $manager->flush();
  }
}
