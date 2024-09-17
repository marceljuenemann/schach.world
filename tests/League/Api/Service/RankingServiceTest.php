<?php

namespace tests\League\Api\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Nsv\League\Api\Service\RankingService;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Team;
use Nsv\League\Api\Model\RankingTeam;

class RankingServiceTest extends KernelTestCase {

  public $rankingService;

  public function setUp(): void {
    self::bootKernel();
    $this->rankingService = self::getContainer()->get(RankingService::class);
  }

  /**
   * Create some RankingTeam Objects with teams and pairings.
   */
  public function createTeamsData() {
    $team1_team = new Team();
    $team1 = new RankingTeam();
    $team1->team = $team1_team;
    $team1->team->id = 1;
    $team1->team->name = 'Panthers';

    $team2_team = new Team();
    $team2 = new RankingTeam();
    $team2->team = $team2_team;
    $team2->team->id = 2;
    $team2->team->name = 'Sharks';

    $team3_team = new Team();
    $team3 = new RankingTeam();
    $team3->team = $team3_team;
    $team3->team->id = 3;
    $team3->team->name = 'Tigers';

    $pairing1 = new Pairing();
    $pairing1->id = 1;
    $pairing1->team1 = $team1->team;
    $pairing1->team2 = $team3->team;
    $pairing1->result1 = floatval(2.5);
    $pairing1->result2 = floatval(5.5);

    $pairing2 = new Pairing();
    $pairing2->id = 1;
    $pairing2->team1 = $team3->team;
    $pairing2->team2 = $team2->team;
    $pairing2->result1 = floatval(4);
    $pairing2->result2 = floatval(4);

    $pairing3 = new Pairing();
    $pairing3->id = 1;
    $pairing3->team1 = $team2->team;
    $pairing3->team2 = $team1->team;
    $pairing3->result1 = floatval(3.5);
    $pairing3->result2 = floatval(4.5);

    $team1->pairings = [$pairing1, $pairing3];
    $team2->pairings = [$pairing2, $pairing3];
    $team3->pairings = [$pairing1, $pairing2];

    return [$team1, $team2, $team3];
  }

  /**
   * @dataProvider teamPairingsDataProvider
   *
   * Test the Method getMPvs if the right team points are returned.
   */
  public function testGetMpvs(RankingTeam $teamCurrent, RankingTeam $teamOpponent, int $expectedResult) {
    $points_from_method = $this->rankingService->getMpvs($teamCurrent, $teamOpponent);
    self::assertSame($expectedResult, $points_from_method);
  }

  public function teamPairingsDataProvider() {
    $teamsWithPairings = $this->createTeamsData();

    yield 'team1 against team3 team points' => [$teamsWithPairings[0], $teamsWithPairings[2], 0];
    yield 'team2 against team3 team points' => [$teamsWithPairings[1], $teamsWithPairings[2], 1];
    yield 'team1 against team2 team points' => [$teamsWithPairings[0], $teamsWithPairings[1], 2];
  }


}