<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Request\CreateOrUpdatePlayerRequest;
use Nsv\League\Api\Request\LateRegistrationRequest;
use Nsv\League\Api\Request\UpdatePairingGameRequest;
use Nsv\League\Api\Request\UpdatePairingRequest;
use Nsv\League\Core\Result;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\Game;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Player;
use Nsv\League\Entity\Team;
use Nsv\WebApp\Core\NsvErrorException;
use Tests\League\CreatesPairing;
use Tests\League\LeagueTestCase;

class PairingServiceTest extends LeagueTestCase
{
  use CreatesPairing;

  private PairingService $service;
  private PlayerService $playerService;
  private League $league;
  private Division $division;
  private int $boardCount;

  protected function setUp(): void {
    parent::setUp();
    $this->service = $this->container->get(PairingService::class);
    $this->playerService = $this->container->get(PlayerService::class);
    $this->league = $this->leagueRepository->findByPathOrPrefix('bezirk1-2526');  // Has substitute teams.
    foreach ($this->league->divisions as $division) {
      if ($division->boardCount() >= 2) $this->division = $division;
    }
    $this->boardCount = $this->division->boardCount();
    $this->createPairing($this->em, $this->league, $this->division);
  }

  protected function tearDown(): void {
    $this->deleteCreatedPairing($this->em);
    parent::tearDown();
  }

  private function request(array $overrides = []): UpdatePairingRequest {
    $request = new UpdatePairingRequest();
    $request->result1 = 0.0;
    $request->result2 = 0.0;
    $request->comment = null;
    $request->games = [];
    for ($board = 1; $board <= $this->boardCount; $board++) {
      $game = new UpdatePairingGameRequest();
      $game->board = $board;
      $game->result1 = Result::UNKNOWN;
      $game->result2 = Result::UNKNOWN;
      $request->games[] = $game;
    }
    foreach ($overrides as $key => $value) {
      $request->$key = $value;
    }
    return $request;
  }

  private function addPlayer(Team $team, string $lastName = 'Mustermann', string $zps = ''): Player {
    $request = new CreateOrUpdatePlayerRequest();
    $request->lastName = $lastName;
    $request->zps = $zps;
    return $this->playerService->createPlayer($team, $request);
  }

  private function lateRegistration(int $teamNumber, string $lastName = 'Neumann', ?string $zps = null): LateRegistrationRequest {
    $lateRegistration = new LateRegistrationRequest();
    $lateRegistration->teamNumber = $teamNumber;
    $lateRegistration->player = new CreateOrUpdatePlayerRequest();
    $lateRegistration->player->lastName = $lastName;
    $lateRegistration->player->zps = $zps;
    return $lateRegistration;
  }

  private function savedGames(): array {
    $this->em->clear();
    return $this->em->getRepository(Game::class)->findBy(['pairing' => $this->pairing->id], ['board' => 'ASC']);
  }

  private function assertRejected(UpdatePairingRequest $request, string $message): void {
    $playerCount = fn() => (int) $this->em->createQuery('SELECT COUNT(p) FROM ' . Player::class . ' p WHERE p.team IN (:teams)')
      ->setParameter('teams', [$this->home, $this->substitute, $this->away])
      ->getSingleScalarResult();
    $playersBefore = $playerCount();

    try {
      $this->service->updatePairing($this->pairing, $request);
      $this->fail('Expected the request to be rejected');
    } catch (NsvErrorException $e) {
      $this->assertStringContainsString($message, $e->getMessage());
      $this->assertEquals(422, $e->getStatusCode());
    }

    // Nothing may have been written, the tables can't roll back.
    $this->assertSame($playersBefore, $playerCount());
    $this->assertCount(0, $this->savedGames());
    $this->em->refresh($this->em->find($this->pairing::class, $this->pairing->id));
    $this->assertNull($this->em->find($this->pairing::class, $this->pairing->id)->result1);
  }

  public function testUpdatePairing_storesResultsAndGames() {
    $player1 = $this->addPlayer($this->home);
    $player2 = $this->addPlayer($this->away);
    $request = $this->request(['result1' => 1.5, 'result2' => 0.5, 'comment' => '  Kampflos 2. Brett  ']);
    $request->games[0]->player1 = $player1->id;
    $request->games[0]->player2 = $player2->id;
    $request->games[0]->result1 = Result::DRAW();
    $request->games[0]->result2 = Result::DRAW();

    $this->service->updatePairing($this->pairing, $request);

    $games = $this->savedGames();
    $this->assertCount($this->boardCount, $games);
    $this->assertEquals($player1->id, $games[0]->player1->id);
    $this->assertEquals($player2->id, $games[0]->player2->id);
    $this->assertSame(Result::DRAW(), $games[0]->result1);
    $this->assertSame(Result::DRAW(), $games[0]->result2);
    $this->assertNull($games[1]->player1);
    $this->assertSame(Result::UNKNOWN, $games[1]->result1);

    $pairing = $this->em->find($this->pairing::class, $this->pairing->id);
    $this->assertSame(1.5, $pairing->result1);
    $this->assertSame(0.5, $pairing->result2);
    $this->assertSame('Kampflos 2. Brett', $pairing->comment);
    $this->assertNotNull($pairing->lastModified);
  }

  public function testUpdatePairing_storesEmptyCommentAsNull() {
    $this->service->updatePairing($this->pairing, $this->request(['comment' => '  ']));
    $this->savedGames();
    $this->assertNull($this->em->find($this->pairing::class, $this->pairing->id)->comment);
  }

  public function testUpdatePairing_storesCommentZero() {
    $this->service->updatePairing($this->pairing, $this->request(['comment' => '0']));
    $this->savedGames();
    $this->assertSame('0', $this->em->find($this->pairing::class, $this->pairing->id)->comment);
  }

  public function testUpdatePairing_replacesExistingGames() {
    $this->service->updatePairing($this->pairing, $this->request());
    $this->assertCount($this->boardCount, $this->savedGames());

    $pairing = $this->em->find($this->pairing::class, $this->pairing->id);
    $request = $this->request(['result1' => 1.0]);
    $request->games[0]->result1 = Result::WIN;
    $this->service->updatePairing($pairing, $request);

    $games = $this->savedGames();
    $this->assertCount($this->boardCount, $games);
    $this->assertSame(Result::WIN, $games[0]->result1);
  }

  public function testUpdatePairing_allowsSubstitutePlayer() {
    $player = $this->addPlayer($this->substitute);
    $request = $this->request();
    $request->games[0]->player1 = $player->id;

    $this->service->updatePairing($this->pairing, $request);

    $this->assertEquals($player->id, $this->savedGames()[0]->player1->id);
  }

  public function testUpdatePairing_keepsPlayerWhoLeftTheTeam() {
    $player = $this->addPlayer($this->home);
    $request = $this->request();
    $request->games[0]->player1 = $player->id;
    $this->service->updatePairing($this->pairing, $request);

    $this->savedGames();
    $player = $this->em->find(Player::class, $player->id);
    $player->team = $this->em->find(Team::class, $this->away->id);
    $this->em->flush();
    $this->em->clear();

    $this->service->updatePairing($this->em->find($this->pairing::class, $this->pairing->id), $request);

    $this->assertEquals($player->id, $this->savedGames()[0]->player1->id);
  }

  public function testUpdatePairing_rejectsWrongBoards() {
    $request = $this->request();
    array_pop($request->games);
    $this->assertRejected($request, "genau {$this->boardCount} Bretter");
  }

  public function testUpdatePairing_rejectsDuplicateBoards() {
    $request = $this->request();
    $request->games[1]->board = 1;
    $this->assertRejected($request, "genau {$this->boardCount} Bretter");
  }

  public function testUpdatePairing_rejectsTooHighResult() {
    $this->assertRejected($this->request(['result2' => $this->boardCount + 0.5]), 'Gesamtergebnis');
  }

  public function testUpdatePairing_rejectsPlayerOfOtherTeam() {
    $request = $this->request();
    $request->games[1]->player1 = $this->addPlayer($this->away)->id;
    $this->assertRejected($request, 'Brett 2: Die Person steht nicht in der Aufstellung');
  }

  public function testUpdatePairing_rejectsDuplicatePlayer() {
    $player = $this->addPlayer($this->home);
    $request = $this->request();
    $request->games[0]->player1 = $player->id;
    $request->games[1]->player1 = $player->id;
    $this->assertRejected($request, 'Brett 2: Die Person spielt bereits');
  }

  public function testUpdatePairing_rejectsPlayerAndLateRegistration() {
    $request = $this->request();
    $request->games[0]->player1 = $this->addPlayer($this->home)->id;
    $request->games[0]->lateRegistration1 = $this->lateRegistration(1);
    $this->assertRejected($request, 'Brett 1: Es kann nur eine Person');
  }

  public function testUpdatePairing_rejectsDuplicateLateRegistration() {
    $request = $this->request();
    $request->games[0]->lateRegistration1 = $this->lateRegistration(1);
    $request->games[1]->lateRegistration1 = $this->lateRegistration(1);
    $this->assertRejected($request, 'Brett 2: Die Person spielt bereits');
  }

  public function testUpdatePairing_rejectsLateRegistrationForUnknownTeam() {
    $request = $this->request();
    $request->games[0]->lateRegistration1 = $this->lateRegistration(5);
    $this->assertRejected($request, 'Brett 1: Die Nachmeldung ist');
  }

  public function testUpdatePairing_rejectsLateRegistrationAlreadyOnTeam() {
    $this->addPlayer($this->home, 'Vorhanden', '70156-1');
    $request = $this->request();
    $request->games[0]->lateRegistration1 = $this->lateRegistration(1, 'Vorhanden', '70156-1');
    $this->assertRejected($request, 'Brett 1: Die Nachmeldung steht bereits');
  }

  public function testUpdatePairing_createsLateRegistrations() {
    $this->addPlayer($this->home, 'Vorhanden');
    $request = $this->request();
    $request->games[0]->lateRegistration1 = $this->lateRegistration(1, 'Eins');
    $request->games[1]->lateRegistration1 = $this->lateRegistration(1, 'Zwei');

    $this->service->updatePairing($this->pairing, $request);

    $games = $this->savedGames();
    $this->assertSame('Eins', $games[0]->player1->lastName);
    $this->assertSame('Zwei', $games[1]->player1->lastName);
    $this->assertEquals($this->home->id, $games[0]->player1->team->id);
    $this->assertEquals(2, $games[0]->player1->number);
    $this->assertEquals(3, $games[1]->player1->number);
    $this->assertEquals(3, $games[0]->player1->lateRegistrationRound);
    $this->assertEquals($this->division->id, $games[0]->player1->lateRegistrationDivision->id);
  }

  public function testUpdatePairing_registersSubstituteForDivisionOfPairing() {
    $request = $this->request();
    $request->games[0]->lateRegistration1 = $this->lateRegistration(2);

    $this->service->updatePairing($this->pairing, $request);

    $player = $this->savedGames()[0]->player1;
    $this->assertEquals($this->substitute->id, $player->team->id);
    $this->assertEquals($this->division->id, $player->lateRegistrationDivision->id);
  }
}
