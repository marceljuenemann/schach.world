<?php

namespace Nsv\League\Api\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Nsv\Dwz\IsewaseDwzCalculator;
use Nsv\League\Api\Request\CreateOrUpdatePlayerRequest;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Player;
use Nsv\League\Entity\Team;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tests\League\LeagueTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;

#[AllowMockObjectsWithoutExpectations]
class PlayerServiceTest extends LeagueTestCase
{
  private MockObject $dwzService;
  private PlayerService $service;
  private League $league;
  private Team $team;

  protected function setUp(): void {
    parent::setUp();
    $this->dwzService = $this->createMock(IsewaseDwzCalculator::class);
    $this->container->set(IsewaseDwzCalculator::class, $this->dwzService);
    $this->service = $this->container->get(PlayerService::class);
    $this->league = $this->leagueRepository->findByPathOrPrefix('nsj-2526');
    $this->team = $this->league->teamById(8412);  // SK Lehrte
  }

  public function testPlayer1() {
    $player = $this->team->players[2];
    $this->dwzService->expects(self::once())
        ->method('calculate')
        ->willReturnCallback(function() {
          return func_get_args();
        });

    $model = $this->service->player($this->league, $player->id);
    $this->assertMatchesSnapshot($model);
  }

  public function testPlayer2() {
    $player = $this->team->players[3];
    $this->dwzService->expects(self::once())
        ->method('calculate')
        ->willReturnCallback(function() {
          return func_get_args();
        });

    $model = $this->service->player($this->league, $player->id);
    $this->assertMatchesSnapshot($model);
  }

  public function testPlayer3_withoutGamesAndRating() {
    $player = $this->team->players[0];
    $this->dwzService->expects(self::never())->method('calculate');
    $model = $this->service->player($this->league, $player->id);
    $this->assertMatchesSnapshot($model);
  }

  private function request(array $overrides = []): CreateOrUpdatePlayerRequest {
    $request = new CreateOrUpdatePlayerRequest();
    $request->lastName = 'Mustermann';
    foreach ($overrides as $key => $value) {
      $request->$key = $value;
    }
    return $request;
  }

  private function newEmptyTeam(int $number): Team {
    $team = new Team();
    $team->league = $this->league;
    $team->division = $this->team->division;
    $team->name = 'Test Team';
    $team->number = $number;
    $team->zps = null;
    $team->venueName = null;
    $team->venueStreet = null;
    $team->venuePostCode = null;
    $team->venueCity = null;
    $team->venuePhone = null;
    $team->players = new ArrayCollection();
    $this->em->persist($team);
    $this->em->flush();
    return $team;
  }

  public function testCreatePlayer_assignsNextBoardNumber() {
    $this->league->configPlayerNumbersWithTeamNumber = false;
    $team = $this->newEmptyTeam(5);
    $existing = $this->service->createPlayer($team, $this->request(['lastName' => 'Eins']));
    $team->players->add($existing);

    $player = $this->service->createPlayer($team, $this->request(['firstName' => 'Max']));

    $this->assertEquals($existing->number + 1, $player->number);
  }

  public function testCreatePlayer_emptyTeamStartsAtOne() {
    $this->league->configPlayerNumbersWithTeamNumber = false;
    $team = $this->newEmptyTeam(50);

    $player = $this->service->createPlayer($team, $this->request());

    $this->assertEquals(1, $player->number);
  }

  public function testCreatePlayer_emptyTeamWithThreeDigitNumbers() {
    $this->league->configPlayerNumbersWithTeamNumber = true;
    $team = $this->newEmptyTeam(2);

    $player = $this->service->createPlayer($team, $this->request());

    $this->assertEquals(201, $player->number);
  }

  public function testCreatePlayer_appliesFields() {
    $player = $this->service->createPlayer($this->team, $this->request([
      'firstName' => 'Max',
      'lastName' => 'Mustermann',
      'title' => 'GM',
      'zps' => '70506-999',
      'dwz' => 1800,
      'elo' => 1750,
      'yearOfBirth' => 1990,
      'gender' => 'M',
    ]));

    $this->assertEquals('Max', $player->firstName);
    $this->assertEquals('Mustermann', $player->lastName);
    $this->assertEquals('GM', $player->title);
    $this->assertEquals('70506-999', $player->zps);
    $this->assertEquals(1800, $player->dwz);
    $this->assertEquals(1750, $player->elo);
    $this->assertEquals('1990', $player->birth);
    // applyRequest() normalizes gender to lowercase to match the entity's constants.
    $this->assertEquals('m', $player->gender);
    $this->assertNull($player->lateRegistrationRound);
    $this->assertNull($player->lateRegistrationDivision);
  }

  public function testCreatePlayer_lateRegistrationDefaultsToTeamDivision() {
    $player = $this->service->createPlayer($this->team, $this->request(['lateRegistrationRound' => 3]));

    $this->assertEquals(3, $player->lateRegistrationRound);
    $this->assertNotNull($player->lateRegistrationDivision);
    $this->assertEquals($this->team->division->id, $player->lateRegistrationDivision->id);
  }

  public function testUpdatePlayer_clearsDivisionWhenRoundCleared() {
    $player = $this->service->createPlayer($this->team, $this->request(['lateRegistrationRound' => 2]));
    $this->assertNotNull($player->lateRegistrationDivision);

    $updated = $this->service->updatePlayer($player, $this->request(['lateRegistrationRound' => null]));

    $this->assertNull($updated->lateRegistrationRound);
    $this->assertNull($updated->lateRegistrationDivision);
  }

  public function testUpdatePlayer_preservesExistingDivisionWhenRoundKept() {
    $player = $this->service->createPlayer($this->team, $this->request(['lateRegistrationRound' => 2]));
    $originalDivisionId = $player->lateRegistrationDivision->id;

    // Changing the round while keeping it set should NOT recompute the division
    // from the team's (possibly since-changed) current division.
    $updated = $this->service->updatePlayer($player, $this->request(['lateRegistrationRound' => 5]));

    $this->assertEquals(5, $updated->lateRegistrationRound);
    $this->assertEquals($originalDivisionId, $updated->lateRegistrationDivision->id);
  }

  public function testUpdatePlayer_defaultsDivisionWhenRoundNewlySet() {
    $player = $this->service->createPlayer($this->team, $this->request());
    $this->assertNull($player->lateRegistrationDivision);

    $updated = $this->service->updatePlayer($player, $this->request(['lateRegistrationRound' => 4]));

    $this->assertEquals(4, $updated->lateRegistrationRound);
    $this->assertNotNull($updated->lateRegistrationDivision);
    $this->assertEquals($this->team->division->id, $updated->lateRegistrationDivision->id);
  }

  public function testDeletePlayer_removesPlayerAndShiftsBoardNumbers() {
    $p1 = $this->service->createPlayer($this->team, $this->request(['lastName' => 'Eins']));
    $p2 = $this->service->createPlayer($this->team, $this->request(['lastName' => 'Zwei']));
    $p3 = $this->service->createPlayer($this->team, $this->request(['lastName' => 'Drei']));
    $p1Number = $p1->number;
    $p2Number = $p2->number;
    $p2Id = $p2->id;

    $this->service->deletePlayer($p2);

    // The bulk DQL update doesn't touch Doctrine's identity map, so refresh explicitly.
    $this->em->refresh($p1);
    $this->em->refresh($p3);

    $this->assertNull($this->em->find(Player::class, $p2Id));
    $this->assertEquals($p1Number, $p1->number);
    $this->assertEquals($p2Number, $p3->number);
  }

  public function testDeletePlayer_throwsWhenPlayerHasGames() {
    $player = $this->team->players[2];

    $this->expectException(ConflictHttpException::class);
    $this->service->deletePlayer($player);
  }

  public function testReorderPlayers_assignsSequentialNumbers() {
    $this->league->configPlayerNumbersWithTeamNumber = false;
    $team = $this->newEmptyTeam(3);
    $p1 = $this->service->createPlayer($team, $this->request(['lastName' => 'Eins']));
    $team->players->add($p1);
    $p2 = $this->service->createPlayer($team, $this->request(['lastName' => 'Zwei']));
    $team->players->add($p2);
    $p3 = $this->service->createPlayer($team, $this->request(['lastName' => 'Drei']));
    $team->players->add($p3);

    $this->service->reorderPlayers($team, [$p3->id, $p1->id, $p2->id]);

    $this->em->refresh($p1);
    $this->em->refresh($p2);
    $this->em->refresh($p3);
    $this->assertEquals(1, $p3->number);
    $this->assertEquals(2, $p1->number);
    $this->assertEquals(3, $p2->number);
  }

  public function testReorderPlayers_appliesThreeDigitNumbering() {
    $this->league->configPlayerNumbersWithTeamNumber = true;
    $team = $this->newEmptyTeam(4);
    $p1 = $this->service->createPlayer($team, $this->request(['lastName' => 'Eins']));
    $team->players->add($p1);
    $p2 = $this->service->createPlayer($team, $this->request(['lastName' => 'Zwei']));
    $team->players->add($p2);

    $this->service->reorderPlayers($team, [$p2->id, $p1->id]);

    $this->em->refresh($p1);
    $this->em->refresh($p2);
    $this->assertEquals(401, $p2->number);
    $this->assertEquals(402, $p1->number);
  }

  public function testReorderPlayers_throwsOnMismatchedPlayerSet() {
    $this->expectException(ConflictHttpException::class);
    $this->service->reorderPlayers($this->team, [999999]);
  }
}
