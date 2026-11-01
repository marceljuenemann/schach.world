<?php

namespace Tests\League\Controller;

use Nsv\League\Api\Request\CreateOrUpdatePlayerRequest;
use Nsv\League\Api\Service\PlayerService;
use Nsv\League\Entity\Player;
use Nsv\League\Entity\Team;
use PHPUnit\Framework\Attributes\DataProvider;

class TeamApiTest extends ApiTestCase
{
  private Player $player;

  protected function setUp(): void {
    parent::setUp();
    $request = new CreateOrUpdatePlayerRequest();
    $request->lastName = 'Mustermann';
    $this->player = $this->client->getContainer()->get(PlayerService::class)->createPlayer($this->home, $request);
  }

  public static function endpoints(): array {
    $venue = ['name' => 'Neu', 'note' => '', 'street' => 'Weg 1', 'postCode' => '30159', 'city' => 'Hannover', 'phone' => ''];
    $captain = ['name' => 'Neu', 'mail' => 'neu@example.org', 'phone' => '', 'phone2' => ''];
    return [
      'name and number' => ['PUT', 'updateNameAndNumber/', ['name' => 'Neuer Name', 'number' => 5]],
      'venue' => ['PUT', 'venue/', $venue],
      'captain' => ['PUT', 'captain/', $captain],
      'recipients' => ['PUT', 'recipients/', ['recipients' => []]],
      'create player' => ['POST', 'players/', ['lastName' => 'Neu']],
      'update player' => ['PUT', 'players/PLAYER/', ['lastName' => 'Neu']],
      'delete player' => ['DELETE', 'players/PLAYER/', null],
      'reorder players' => ['PUT', 'players/reorder/', ['playerIds' => ['PLAYER']]],
    ];
  }

  private function callEndpoint(string $method, string $path, ?array $payload, $league): void {
    $path = str_replace('PLAYER', (string) $this->player->id, $path);
    if (isset($payload['playerIds'])) {
      $payload['playerIds'] = [$this->player->id];
    }
    $this->api($method, "teams/{$this->home->id}/$path", $payload, $league);
  }

  private function assertTeamUnchanged(): void {
    $this->em->clear();
    $team = $this->em->find(Team::class, $this->home->id);
    $this->assertSame('Test Team', $team->name);
    $this->assertSame(1, $team->number);
    $this->assertNull($team->venueName);
    $this->assertCount(1, $team->players);
    $this->assertSame('Mustermann', $team->players[0]->lastName);
  }

  #[DataProvider('endpoints')]
  public function testEndpoint_worksForLeagueManager(string $method, string $path, ?array $payload) {
    $this->login($this->league);
    $this->callEndpoint($method, $path, $payload, $this->league);
    $this->assertResponseIsSuccessful();
  }

  #[DataProvider('endpoints')]
  public function testEndpoint_rejectsTeamOfOtherLeague_forLeagueManager(string $method, string $path, ?array $payload) {
    $this->login($this->otherLeague);
    $this->callEndpoint($method, $path, $payload, $this->otherLeague);
    $this->assertResponseStatusCodeSame(404);
    $this->assertTeamUnchanged();
  }

  #[DataProvider('endpoints')]
  public function testEndpoint_rejectsTeamOfOtherLeague_forDivisionManager(string $method, string $path, ?array $payload) {
    $this->login($this->otherLeague, $this->otherLeague->divisions[0]);
    $this->callEndpoint($method, $path, $payload, $this->otherLeague);
    $this->assertResponseStatusCodeSame(404);
    $this->assertTeamUnchanged();
  }

  #[DataProvider('endpoints')]
  public function testEndpoint_rejectsTeamWithDivisionOfOtherLeague(string $method, string $path, ?array $payload) {
    $this->home->divisionId = $this->division->id;
    $this->em->flush();
    $this->login($this->otherLeague);
    $this->callEndpoint($method, $path, $payload, $this->otherLeague);
    $this->assertResponseStatusCodeSame(404);
    $this->assertTeamUnchanged();
  }

  public function testEndpoint_returnsNotFoundForInvalidTeamId() {
    $this->login($this->league);
    $this->api('PUT', 'teams/abc/venue/', ['name' => 'Neu']);
    $this->assertResponseStatusCodeSame(404);
  }
}
