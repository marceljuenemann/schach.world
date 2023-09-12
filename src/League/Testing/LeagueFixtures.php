<?php

namespace Nsv\League\Testing;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Nsv\League\Core\Encoding;
use Nsv\League\Core\Result;
use Nsv\League\Entity\Date;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\Game;
use Nsv\League\Entity\League;
use Nsv\League\Entity\LegacyUser;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Player;
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
    $league->organisation = '7';
    $league->manager = new LegacyUser();
    $league->manager->name = 'League Admin';
    $league->manager->password = md5('123456');
    $league->manager->mail = 'league@example.com';
    $manager->persist($league->manager);
    $manager->persist($league);

    /////////////////////////////////////
    // DIVISIONS
    /////////////////////////////////////

    $division = new Division();
    $division->league = $league;
    $division->name = "Test Division";
    $division->sortId = 1;
    $division->manager = new LegacyUser();
    $division->manager->name = 'Division Admin';
    $division->manager->password = md5('654321');
    $division->manager->mail = 'division@example.com';
    $manager->persist($division->manager);
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
    $pairing1a->lastModified = date('Y-m-d H:i:s');
    $manager->persist($pairing1a);

    // PAIRING 1B: Match without result.
    $pairing1b = new Pairing();
    $pairing1b->division = $division;
    $pairing1b->round = 1;
    $pairing1b->team1 = $team2;
    $pairing1b->team2 = $team1;
    $pairing1b->lastModified = date('Y-m-d H:i:s');
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
    $pairing2->team1 = $team2;
    $pairing2->team2 = $team1;
    $pairing2->lastModified = date('Y-m-d H:i:s');
    $manager->persist($pairing2);

    // ROUND 3: Round with a date, but no games.
    $date3 = new Date();
    $date3->league = $league;
    $date3->round = 3;
    $date3->date = '2025-03-03';
    $manager->persist($date3);

    // ROUND 4: Round with the same date.
    $date4 = new Date();
    $date4->league = $league;
    $date4->round = 4;
    $date4->date = '2025-03-03';
    $manager->persist($date4);

    // PAIRING 4A: Custom date
    $pairing4A = new Pairing();
    $pairing4A->division = $division;
    $pairing4A->round = 4;
    $pairing4A->team1 = $team1;
    $pairing4A->team2 = $team2;
    $pairing4A->customDate = '2025-04-05';
    $pairing4A->lastModified = date('Y-m-d H:i:s');
    $manager->persist($pairing4A);

    // PAIRING 4B: Moved without date set
    $pairing4B = new Pairing();
    $pairing4B->division = $division;
    $pairing4B->round = 4;
    $pairing4B->team1 = $team1;
    $pairing4B->team2 = $team2;
    $pairing4B->customDate = Pairing::UNKNOWN_DATE;
    $pairing4B->lastModified = date('Y-m-d H:i:s');
    $manager->persist($pairing4B);

    // PAIRING 4C: Custom host.
    $pairing4C = new Pairing();
    $pairing4C->division = $division;
    $pairing4C->round = 4;
    $pairing4C->team1 = $team1;
    $pairing4C->team2 = $team2;
    $pairing4C->host = $team2;
    $pairing4C->lastModified = date('Y-m-d H:i:s');
    $manager->persist($pairing4C);

    /////////////////////////////////////
    // PLAYERS
    /////////////////////////////////////

    // PLAYER 1: With title and everything
    $player1 = new Player();
    $player1->team = $team1;
    $player1->number = 1;
    $player1->lastName = Encoding::utf8_decode('Jünemann');
    $player1->firstName = 'Marcel';
    $player1->title = 'GM Dr.';
    $player1->zps = '70156-117';
    $player1->dwz = 1942;
    $player1->elo = 2020;
    $player1->gender = Player::GENDER_FEMALE;
    $player1->birth = '1989';
    $manager->persist($player1);

    // PLAYER 2: Without title, with rating
    $player2 = new Player();
    $player2->team = $team2;
    $player2->number = 201;
    $player2->lastName = 'Salzmann';
    $player2->firstName = 'Jan';
    $player2->title = '';
    $player2->zps = '70101-1023';
    $player2->dwz = 1500;
    $player2->elo = null;
    $player2->gender = Player::GENDER_MALE;
    $player2->birth = '18.03.2004';
    $manager->persist($player2);

    // PLAYER 3: Without any games or rating
    $player3 = new Player();
    $player3->team = $team1;
    $player3->number = 2;
    $player3->lastName = 'Spiellos';
    $player3->firstName = 'Max';
    $manager->persist($player3);
    
    /////////////////////////////////////
    // GAMES
    /////////////////////////////////////

    // ROUND 1: Win, draw, loss
    $game1 = new Game();
    $game1->pairing = $pairing1a;
    $game1->board = 1;
    $game1->player1 = $player1;
    $game1->player2 = $player2;
    $game1->result1 = Result::WIN;
    $game1->result2 = Result::LOSS;
    $manager->persist($game1);
    
    $game2 = new Game();
    $game2->pairing = $pairing1a;
    $game2->board = 2;
    $game2->player1 = $player1;
    $game2->player2 = $player2;
    $game2->result1 = Result::DRAW();
    $game2->result2 = Result::DRAW();
    $manager->persist($game2);

    $game3 = new Game();
    $game3->pairing = $pairing1a;
    $game3->board = 3;
    $game3->player1 = $player1;
    $game3->player2 = $player2;
    $game3->result1 = Result::LOSS;
    $game3->result2 = Result::WIN;
    $manager->persist($game3);
     
    // ROUND 2: Bye, unknown, unset 
    $game4 = new Game();
    $game4->pairing = $pairing2;
    $game4->board = 1;
    $game4->player1 = $player2;
    $game4->player2 = $player1;
    $game4->result1 = Result::BYE_WIN;
    $game4->result2 = Result::BYE_LOSS;
    $manager->persist($game4);
    
    $game5 = new Game();
    $game5->pairing = $pairing2;
    $game5->board = 2;
    $game5->player1 = $player2;
    $game5->player2 = $player1;
    $game5->result1 = Result::UNKNOWN;
    $game5->result2 = Result::UNKNOWN;
    $manager->persist($game5);
    
    $game6 = new Game();
    $game6->pairing = $pairing2;
    $game6->board = 3;
    $game6->player1 = $player2;
    $game6->player2 = $player1;
    $game6->result1 = Result::UNKNOWN;
    $game6->result2 = Result::UNKNOWN;
    $manager->persist($game6);

    // Game against NULL player.
    $game7 = new Game();
    $game7->pairing = $pairing2;
    $game7->board = 4;
    $game7->player1 = $player2;
    $game7->player2 = null;
    $game7->result1 = Result::BYE_WIN;
    $game7->result2 = Result::BYE_LOSS;
    $manager->persist($game7);
    

    $manager->flush();
  }
}
