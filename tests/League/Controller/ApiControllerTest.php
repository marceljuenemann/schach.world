<?php

namespace Tests\League\Controller;

use Doctrine\DBAL\Connection;
use Nsv\League\Core\LegacySystem;
use Tests\League\LeagueWebTestCase;
use Tests\League\TemporaryLeague;

/**
 * Tests for the mutating API endpoints. All modifications happen on a TemporaryLeague.
 */
class ApiControllerTest extends LeagueWebTestCase
{
  private Connection $db;
  private TemporaryLeague $tmp;
  private string $base;

  protected function setUp(): void {
    parent::setUp();
    $this->db = static::getContainer()->get('doctrine.dbal.league_connection');
    $this->tmp = new TemporaryLeague($this->db);
    $this->base = "/ligen/{$this->tmp->path}/api/";
  }

  protected function tearDown(): void {
    $this->tmp->cleanup();
    parent::tearDown();
  }

  private function loginAsLeagueManager(): void {
    $this->login(league: $this->league($this->tmp->path));
  }

  private function loginAsDivisionManager(int $divisionIndex): void {
    $league = $this->league($this->tmp->path);
    $this->login($league->divisionById($this->tmp->divisionIds[$divisionIndex]));
  }

  private function teamToken(int $teamId): string {
    static::getContainer()->get(LegacySystem::class)->initialize();
    global $globals;
    return substr(md5("MID$teamId$globals[salt]"), 0, $globals['md5-length']);
  }

  private function teamRow(int $teamId): array {
    return $this->db->fetchAssociative('SELECT * FROM mannschaften WHERE id = ?', [$teamId]);
  }

  private function playerRows(int $teamId): array {
    return $this->db->fetchAllAssociative('SELECT * FROM spieler WHERE mannschaft = ? ORDER BY brettnr', [$teamId]);
  }

  private function createPlayer(int $teamId, string $lastName): int {
    $this->request('POST', "{$this->base}teams/$teamId/players/", json: ['lastName' => $lastName, 'firstName' => 'Test']);
    $this->assertResponseIsSuccessful();
    return (int) $this->db->fetchOne('SELECT id FROM spieler WHERE mannschaft = ? AND nachname = ?', [$teamId, $lastName]);
  }

  // Pairings (unstable API)

  public function testPairings_requiresLogin() {
    $this->request('GET', '/ligen/nsv-2526/api/unstable/pairings/');
    $this->assertResponseStatusCodeSame(403);
  }

  public function testPairings_expiredSession() {
    $this->login(loginTime: time() - 9 * 60 * 60);
    $response = $this->request('GET', '/ligen/nsv-2526/api/unstable/pairings/');
    $this->assertResponseStatusCodeSame(403);
    $this->assertHtmlContains('Sitzung abgelaufen', $response);
  }

  public function testPairings_sessionForOtherLeague() {
    $this->login(league: $this->league($this->tmp->path));
    $response = $this->request('GET', '/ligen/nsv-2526/api/unstable/pairings/');
    $this->assertResponseStatusCodeSame(403);
    $this->assertHtmlContains('Kein Zugriff auf diese Liga', $response);
  }

  public function testPairings_asManager() {
    $this->login();
    $response = $this->request('GET', '/ligen/nsv-2526/api/unstable/pairings/');

    $this->assertResponseIsSuccessful();
    $divisions = json_decode($response->getContent(), true);
    $this->assertSame('Landesliga Nord', $divisions[0]['name']);
    $this->assertCount(9, $divisions[0]['matchDays']);
    $this->assertSame('2026-04-26', $divisions[0]['closestDate']);
  }

  // Divisions

  public function testCreateDivision() {
    $this->loginAsLeagueManager();
    $this->request('POST', "{$this->base}divisions/create/", json: [
      'name' => 'Neue Staffel',
      'managerName' => 'Max Mustermann',
      'managerMail' => 'max@example.com',
      'managerPassword' => 'geheim123',
    ]);

    $this->assertResponseIsSuccessful();
    $division = $this->db->fetchAssociative('SELECT * FROM staffeln WHERE turnier = ? AND name = ?', [$this->tmp->id, 'Neue Staffel']);
    $this->assertNotFalse($division);
    $this->assertSame(2, (int) $division['sortid']);
    $manager = $this->db->fetchAssociative('SELECT * FROM benutzer WHERE id = ?', [$division['leiter']]);
    $this->assertSame('Max Mustermann', $manager['name']);
    $this->assertSame(md5('geheim123'), $manager['passwort']);
  }

  public function testCreateDivision_validationError() {
    $this->loginAsLeagueManager();
    $response = $this->request('POST', "{$this->base}divisions/create/", json: [
      'name' => 'X',
      'managerName' => 'Max Mustermann',
      'managerMail' => 'not-an-email',
      'managerPassword' => 'geheim123',
    ]);

    $this->assertResponseStatusCodeSame(422);
    $data = json_decode($response->getContent(), true);
    $this->assertSame('nsv', $data['errorType']);
    $this->assertArrayHasKey('name', $data['validationErrors']);
    $this->assertArrayHasKey('managerMail', $data['validationErrors']);
    $this->assertArrayNotHasKey('managerPassword', $data['validationErrors']);
  }

  public function testCreateDivision_deniedForDivisionManager() {
    $this->loginAsDivisionManager(0);
    $response = $this->request('POST', "{$this->base}divisions/create/", json: [
      'name' => 'Neue Staffel',
      'managerName' => 'Max Mustermann',
      'managerMail' => 'max@example.com',
      'managerPassword' => 'geheim123',
    ]);
    $this->assertResponseStatusCodeSame(403);
    $this->assertHtmlContains('Turnierleiter:innen Zugriff erforderlich.', $response);
  }

  public function testReorderDivisions() {
    $this->loginAsLeagueManager();
    [$a, $b] = $this->tmp->divisionIds;
    $this->request('PUT', "{$this->base}divisions/order/", json: ['divisionIds' => [$b, $a]]);

    $this->assertResponseIsSuccessful();
    $this->assertSame(0, (int) $this->db->fetchOne('SELECT sortid FROM staffeln WHERE id = ?', [$b]));
    $this->assertSame(1, (int) $this->db->fetchOne('SELECT sortid FROM staffeln WHERE id = ?', [$a]));
  }

  // Teams

  public function testUpdateTeamNameAndNumber() {
    $this->loginAsDivisionManager(0);
    $teamId = $this->tmp->firstTeamId();
    $this->request('PUT', "{$this->base}teams/$teamId/updateNameAndNumber/", json: ['name' => 'Umbenannt', 'number' => 3]);

    $this->assertResponseIsSuccessful();
    $row = $this->teamRow($teamId);
    $this->assertSame('Umbenannt', $row['name']);
    $this->assertSame(3, (int) $row['mnr']);
  }

  public function testUpdateTeamNameAndNumber_deniedForOtherDivisionManager() {
    $this->loginAsDivisionManager(1);
    $teamId = $this->tmp->firstTeamId();
    $response = $this->request('PUT', "{$this->base}teams/$teamId/updateNameAndNumber/", json: ['name' => 'Umbenannt', 'number' => 3]);

    $this->assertResponseStatusCodeSame(403);
    $this->assertHtmlContains('Kein Zugriff auf diese Staffel', $response);
    $this->assertSame('Team 0', $this->teamRow($teamId)['name']);
  }

  public function testUpdateTeamVenue_withToken() {
    $teamId = $this->tmp->firstTeamId();
    $this->request('PUT', "{$this->base}teams/$teamId/venue/?auth=" . $this->teamToken($teamId), json: [
      'name' => 'Spiellokal',
      'note' => 'Hinterer Eingang',
      'street' => 'Hauptstraße 1',
      'postCode' => '30159',
      'city' => 'Hannover',
      'phone' => '0511 999',
    ]);

    $this->assertResponseIsSuccessful();
    $row = $this->teamRow($teamId);
    $this->assertSame('Spiellokal', $row['so_name']);
    $this->assertSame('30159', $row['so_plz']);
    $this->assertSame('Hannover', $row['so_stadt']);
    // Stored in latin1.
    $this->assertSame(mb_convert_encoding('Hauptstraße 1', 'ISO-8859-1', 'UTF-8'), $row['so_strasse']);
  }

  public function testUpdateTeamVenue_wrongToken() {
    $teamId = $this->tmp->firstTeamId();
    $this->request('PUT', "{$this->base}teams/$teamId/venue/?auth=wrong", json: ['name' => 'Spiellokal']);
    $this->assertResponseStatusCodeSame(403);
  }

  public function testUpdateTeamCaptain_withTokenHeader() {
    $teamId = $this->tmp->firstTeamId();
    $this->request('PUT', "{$this->base}teams/$teamId/captain/", json: [
      'name' => 'Neuer Kapitän',
      'mail' => 'neu@example.com',
      'phone' => '0511 1',
      'phone2' => '0511 2',
    ], headers: ['HTTP_X_AUTH' => $this->teamToken($teamId)]);

    $this->assertResponseIsSuccessful();
    $row = $this->teamRow($teamId);
    $this->assertSame('neu@example.com', $row['mf_email']);
    $this->assertSame('0511 2', $row['mf_telefon2']);
  }

  public function testUpdateTeamCaptain_asDivisionManagerWithoutToken() {
    $this->loginAsDivisionManager(0);
    $teamId = $this->tmp->firstTeamId();
    $this->request('PUT', "{$this->base}teams/$teamId/captain/", json: [
      'name' => 'Neuer Kapitän',
      'mail' => 'neu@example.com',
      'phone' => '',
      'phone2' => '',
    ]);
    $this->assertResponseIsSuccessful();
    $this->assertSame('neu@example.com', $this->teamRow($teamId)['mf_email']);
  }

  public function testUpdateTeamRecipients() {
    $teamId = $this->tmp->firstTeamId();
    $this->request('PUT', "{$this->base}teams/$teamId/recipients/?auth=" . $this->teamToken($teamId), json: [
      'recipients' => ['a@example.com', 'b@example.com'],
    ]);

    $this->assertResponseIsSuccessful();
    $this->assertSame(
      ['a@example.com', 'b@example.com'],
      $this->db->fetchFirstColumn('SELECT email FROM zusatzempfaenger WHERE mannschaft = ? ORDER BY id', [$teamId])
    );
  }

  public function testUpdateTeamRecipients_validationError() {
    $teamId = $this->tmp->firstTeamId();
    $response = $this->request('PUT', "{$this->base}teams/$teamId/recipients/?auth=" . $this->teamToken($teamId), json: [
      'recipients' => ['not-an-email'],
    ]);
    $this->assertResponseStatusCodeSame(422);
    $this->assertArrayHasKey('recipients[0]', json_decode($response->getContent(), true)['validationErrors']);
  }

  // Players

  public function testCreateUpdateReorderAndDeletePlayers() {
    $this->loginAsDivisionManager(0);
    $teamId = $this->tmp->firstTeamId();

    $first = $this->createPlayer($teamId, 'Erster');
    $second = $this->createPlayer($teamId, 'Zweiter');
    $this->assertSame(['Erster', 'Zweiter'], array_column($this->playerRows($teamId), 'nachname'));

    $this->request('PUT', "{$this->base}teams/$teamId/players/$first/", json: ['lastName' => 'Geändert', 'firstName' => 'Test', 'dwz' => 1500]);
    $this->assertResponseIsSuccessful();
    $row = $this->db->fetchAssociative('SELECT * FROM spieler WHERE id = ?', [$first]);
    $this->assertSame(mb_convert_encoding('Geändert', 'ISO-8859-1', 'UTF-8'), $row['nachname']);
    $this->assertSame(1500, (int) $row['dwz']);

    $this->request('PUT', "{$this->base}teams/$teamId/players/reorder/", json: ['playerIds' => [$second, $first]]);
    $this->assertResponseIsSuccessful();
    $this->assertSame([$second, $first], array_map('intval', array_column($this->playerRows($teamId), 'id')));

    $this->request('DELETE', "{$this->base}teams/$teamId/players/$second/");
    $this->assertResponseIsSuccessful();
    $this->assertSame([$first], array_map('intval', array_column($this->playerRows($teamId), 'id')));
  }

  public function testCreatePlayer_validationError() {
    $this->loginAsDivisionManager(0);
    $teamId = $this->tmp->firstTeamId();
    $response = $this->request('POST', "{$this->base}teams/$teamId/players/", json: ['lastName' => 'X', 'zps' => 'invalid']);

    $this->assertResponseStatusCodeSame(422);
    $errors = json_decode($response->getContent(), true)['validationErrors'];
    $this->assertArrayHasKey('lastName', $errors);
    $this->assertArrayHasKey('zps', $errors);
    $this->assertSame([], $this->playerRows($teamId));
  }

  public function testCreatePlayer_requiresLogin() {
    $teamId = $this->tmp->firstTeamId();
    $this->request('POST', "{$this->base}teams/$teamId/players/", json: ['lastName' => 'Unbefugt']);
    $this->assertResponseStatusCodeSame(403);
    $this->assertSame([], $this->playerRows($teamId));
  }

  public function testUpdateAndDeletePlayer_playerOfOtherTeam() {
    $this->loginAsLeagueManager();
    $teamId = $this->tmp->firstTeamId();
    $otherTeamId = $this->tmp->teamIds[$this->tmp->divisionIds[1]];
    $player = $this->createPlayer($otherTeamId, 'Fremd');

    $this->request('PUT', "{$this->base}teams/$teamId/players/$player/", json: ['lastName' => 'Geändert']);
    $this->assertResponseStatusCodeSame(404);

    $this->request('DELETE', "{$this->base}teams/$teamId/players/$player/");
    $this->assertResponseStatusCodeSame(404);
    $this->assertCount(1, $this->playerRows($otherTeamId));
  }
}
