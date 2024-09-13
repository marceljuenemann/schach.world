<?php

namespace tests\League\Api\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Nsv\League\Api\Service\RankingService;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Team;

class RankingServiceTest extends KernelTestCase {

  public $teamsWithPairings;
  public function setUp(): void {
    self::bootKernel();

    $team1 = new Team();
    $team1->id = 1;
    $team1->name = 'Panthers';

    $team2 = new Team();
    $team2->id = 2;
    $team2->name = 'Sharks';

    $team3 = new Team();
    $team3->id = 3;
    $team3->name = 'Tigers';

    $pairing1 = new Pairing();
    $pairing1->id = 1;
    $pairing1->team1 = $team1;
    $pairing1->team2 = $team3;
    $pairing1->result1 = floatval(2.5);
    $pairing1->result2 = floatval(5.5);

    $pairing2 = new Pairing();
    $pairing2->id = 1;
    $pairing2->team1 = $team3;
    $pairing2->team2 = $team2;
    $pairing2->result1 = floatval(4);
    $pairing2->result2 = floatval(4);

    $pairing3 = new Pairing();
    $pairing3->id = 1;
    $pairing3->team1 = $team2;
    $pairing3->team2 = $team1;
    $pairing3->result1 = floatval(3.5);
    $pairing3->result2 = floatval(4.5);

    $team1->pairings = [$pairing1, $pairing3];
    $team2->pairings = [$pairing2, $pairing3];
    $team3->pairings = [$pairing1, $pairing2];

    $this->teamsWithPairings = [$team1, $team2, $team3];
  }


  public function testGetMpvs() {
    $teamsWithPairings = $this->teamsWithPairings;

    $ulter = 2;



  }


}