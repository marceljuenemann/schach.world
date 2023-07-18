<?php

namespace Nsv\League\Testing;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nsv\League\Entity\League;

/**
 * Creates test data for the league manager.
 */
class LeagueFixtures extends Fixture implements ORMFixtureInterface
{
  public function load(ObjectManager $manager): void {
    $league = new League();
    $league->name = "Test League";
    $manager->persist($league);

    $manager->flush();
  }
}
