<?php

namespace Nsv\League\Testing;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nsv\League\Entity\Date;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Team;

/**
 * Creates test data for the league manager.
 * 
 * TODO: Obviously more specific tests would be the best practice, but this is
 * quick to do and much better than no tests :)
 */
class LeagueFixtures extends Fixture
{
  // TODO: Structure this better.
  public function load(ObjectManager $manager): void {
    $league = new League();
    $league->name = "Test League";
    $league->path = "test";
    $manager->persist($league);

    /////////////////////////////////////
    // DIVISIONS
    /////////////////////////////////////

    $division = new Division();
    $division->league = $league;
    $division->name = "Test Division";
    $division->sortId = 1;
    $manager->persist($division);

    /////////////////////////////////////
    // TEAMS
    /////////////////////////////////////

    $team1 = new Team();
    $team1->league = $league;
    $team1->division = $division;
    $team1->name = 'Team';
    $team1->number = 1;
    $manager->persist($team1);
    
    $team2 = new Team();
    $team2->league = $league;
    $team2->division = $division;
    $team2->name = 'Team';
    $team2->number = 2;
    $manager->persist($team2);

    /////////////////////////////////////
    // MATCH DAYS
    /////////////////////////////////////

    // ROUND 1: basic case with two games.
    $date1 = new Date();
    $date1->league = $league;
    $date1->round = 1;
    $date1->date = '2025-01-01';
    $manager->persist($date1);

    // PAIRING 1A: Match with result.
    $pairing1a = new Pairing();
    $pairing1a->division = $division;
    $pairing1a->round = 1;
    $pairing1a->team1 = $team1;
    $pairing1a->team2 = $team2;
    $pairing1a->result1 = 2.5;
    $pairing1a->result2 = 1.5;
    $manager->persist($pairing1a);

    // PAIRING 1B: Match without result.
    $pairing1b = new Pairing();
    $pairing1b->division = $division;
    $pairing1b->round = 1;
    $pairing1b->team1 = $team2;
    $pairing1b->team2 = $team1;
    $manager->persist($pairing1b);

    // ROUND 2:
    // - Date overriden by the division.
    // - Date of round 2 before round 1 (COVID case).
    $date2 = new Date();
    $date2->league = $league;
    $date2->round = 2;
    $date2->date = '2025-02-02';
    $manager->persist($date2);

    $date2b = new Date();
    $date2b->league = $league;
    $date2b->division = $division;
    $date2b->round = 2;
    $date2b->date = '2024-12-31';
    $manager->persist($date2b);

    $pairing2 = new Pairing();
    $pairing2->division = $division;
    $pairing2->round = 2;
    $pairing2->team1 = $team1;
    $pairing2->team2 = $team2;
    $manager->persist($pairing2);

    // ROUND 3: Round with a date, but no games.
    $date3 = new Date();
    $date3->league = $league;
    $date3->round = 3;
    $date3->date = '2025-03-03';
    $manager->persist($date3);

    // ROUND 4: Round without a date.
    $date4 = new Date();
    $date4->league = $league;
    $date4->round = 4;
    $date4->date = '2025-04-04';
    $manager->persist($date4);

    // PAIRING 4A: Custom date
    $pairing4A = new Pairing();
    $pairing4A->division = $division;
    $pairing4A->round = 4;
    $pairing4A->team1 = $team1;
    $pairing4A->team2 = $team2;
    $pairing4A->customDate = '2025-04-05';
    $manager->persist($pairing4A);

    // PAIRING 4B: Moved without date set
    $pairing4B = new Pairing();
    $pairing4B->division = $division;
    $pairing4B->round = 4;
    $pairing4B->team1 = $team1;
    $pairing4B->team2 = $team2;
    $pairing4B->customDate = Pairing::UNKNOWN_DATE;
    $manager->persist($pairing4B);

    // PAIRING 4C: Custom host.
    $pairing4C = new Pairing();
    $pairing4C->division = $division;
    $pairing4C->round = 4;
    $pairing4C->team1 = $team1;
    $pairing4C->team2 = $team2;
    $pairing4C->host = $team2;
    $manager->persist($pairing4C);

    $manager->flush();
  }
}
