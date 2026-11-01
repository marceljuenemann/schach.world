<?php

namespace Tests\League\Controller;

use Nsv\League\Core\Result;
use Nsv\League\Entity\Game;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Pairing;

class PairingApiTest extends ApiTestCase
{
  private function payload(array $overrides = []): array {
    $games = [];
    for ($board = 1; $board <= $this->division->boardCount(); $board++) {
      $games[] = ['board' => $board, 'player1' => null, 'lateRegistration1' => null, 'player2' => null, 'lateRegistration2' => null, 'result1' => '½', 'result2' => '½'];
    }
    return ['result1' => 1, 'result2' => 1.5, 'comment' => 'Bemerkung <b>ÄÖ</b>', 'sendConfirmation' => true, 'games' => $games, ...$overrides];
  }

  private function put(array $payload, ?League $league = null): array {
    return $this->api('PUT', "pairings/{$this->pairing->id}/", $payload, $league);
  }

  private function savedGames(): array {
    $this->em->clear();
    return $this->em->getRepository(Game::class)->findBy(['pairing' => $this->pairing->id]);
  }

  public function testUpdatePairing_asLeagueManager() {
    $this->login($this->league);

    $this->put($this->payload());

    $this->assertResponseIsSuccessful();
    $games = $this->savedGames();
    $this->assertCount($this->division->boardCount(), $games);
    $this->assertSame(Result::DRAW(), $games[0]->result1);
    $pairing = $this->em->find(Pairing::class, $this->pairing->id);
    $this->assertSame(1.0, $pairing->result1);
    $this->assertSame(1.5, $pairing->result2);
    $this->assertSame(mb_convert_encoding('Bemerkung <b>ÄÖ</b>', 'ISO-8859-1', 'UTF-8'), $pairing->comment);
  }

  public function testUpdatePairing_asDivisionManager() {
    $this->login($this->league, $this->division);
    $this->put($this->payload());
    $this->assertResponseIsSuccessful();
  }

  public function testUpdatePairing_withLateRegistration() {
    $this->login($this->league);
    $payload = $this->payload();
    $payload['games'][1]['lateRegistration2'] = ['teamNumber' => 1, 'player' => ['firstName' => 'Jürgen', 'lastName' => 'Müller', 'gender' => 'm', 'yearOfBirth' => 1980]];

    $this->put($payload);

    $this->assertResponseIsSuccessful();
    foreach ($this->savedGames() as $game) {
      if ($game->board === 2) {
        $this->assertSame(mb_convert_encoding('Müller', 'ISO-8859-1', 'UTF-8'), $game->player2->lastName);
        return;
      }
    }
    $this->fail('Board 2 not found');
  }

  public function testUpdatePairing_requiresLogin() {
    $this->put($this->payload());
    $this->assertResponseStatusCodeSame(403);
    $this->assertCount(0, $this->savedGames());
  }

  public function testUpdatePairing_rejectsManagerOfOtherDivision() {
    foreach ($this->league->divisions as $division) {
      if ($division->id !== $this->division->id) $otherDivision = $division;
    }
    $this->login($this->league, $otherDivision);

    $this->put($this->payload());

    $this->assertResponseStatusCodeSame(403);
    $this->assertCount(0, $this->savedGames());
  }

  public function testUpdatePairing_rejectsPairingOfOtherLeague() {
    $this->login($this->otherLeague);

    $this->put($this->payload(), $this->otherLeague);

    $this->assertResponseStatusCodeSame(404);
    $this->assertCount(0, $this->savedGames());
  }

  public function testUpdatePairing_returnsValidationErrors() {
    $this->login($this->league);
    $payload = $this->payload(['comment' => str_repeat('a', 201), 'result1' => 0.3]);
    $payload['games'][0]['result1'] = 'x';

    $error = $this->put($payload);

    $this->assertResponseStatusCodeSame(422);
    $this->assertSame('nsv', $error['errorType']);
    $this->assertArrayHasKey('comment', $error['validationErrors']);
    $this->assertArrayHasKey('result1', $error['validationErrors']);
    $this->assertArrayHasKey('games[0].result1', $error['validationErrors']);
    $this->assertCount(0, $this->savedGames());
  }

  public function testUpdatePairing_requiresSendConfirmation() {
    $this->login($this->league);
    $payload = $this->payload();
    unset($payload['sendConfirmation']);

    $this->put($payload);

    $this->assertResponseStatusCodeSame(422);
    $this->assertCount(0, $this->savedGames());
  }

  public function testUpdatePairing_returnsErrorMessages() {
    $this->login($this->league);

    $error = $this->put($this->payload(['games' => []]));

    $this->assertResponseStatusCodeSame(422);
    $this->assertSame('nsv', $error['errorType']);
    $this->assertStringContainsString('Bretter', $error['errorMessages'][0]);
  }
}
