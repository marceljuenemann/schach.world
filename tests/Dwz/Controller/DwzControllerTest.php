<?php

namespace Tests\Dwz\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The DWZ tables are empty in CI, so this test inserts its own clubs and players
 * (with ZPS numbers that don't exist in reality) and deletes them in tearDown().
 */
class DwzControllerTest extends WebTestCase
{
  private KernelBrowser $client;
  private Connection $db;

  protected function setUp(): void {
    $this->client = static::createClient();
    $this->db = static::getContainer()->get('doctrine.dbal.main_connection');
    $this->cleanup();

    $this->db->insert('dwz_vereine', ['ZPS' => 'X0001', 'LV' => '', 'Verband' => '', 'Vereinname' => 'Zzphpunit Schachclub']);
    $this->db->insert('dwz_vereine', ['ZPS' => 'X0002', 'LV' => '', 'Verband' => '', 'Vereinname' => 'Zzphpunit Schachfreunde']);
    $this->db->insert('dwz_vereine', ['ZPS' => 'Y0001', 'LV' => '', 'Verband' => '', 'Vereinname' => 'Zzphpunit Andere']);
    $this->insertPlayer('X0001', '1', 'A', 'Zzphpunit,Anna', 'W', 'WF', 2100);
    $this->insertPlayer('X0002', '2', 'A', 'Zzphpunit,Bernd', null, null, 1500);
    $this->insertPlayer('X0002', '3', 'P', 'Zzphpunit,Anna', 'W', 'WF', 2100);
  }

  protected function tearDown(): void {
    $this->cleanup();
    parent::tearDown();
  }

  private function cleanup(): void {
    $this->db->executeStatement("DELETE FROM dwz_spieler WHERE ZPS IN ('X0001', 'X0002')");
    $this->db->executeStatement("DELETE FROM dwz_vereine WHERE ZPS IN ('X0001', 'X0002', 'Y0001')");
  }

  private function insertPlayer(string $zps, string $memberId, string $status, string $name, ?string $gender, ?string $title, int $dwz): void {
    $this->db->insert('dwz_spieler', [
      'ZPS' => $zps,
      'Mgl_Nr' => $memberId,
      'Status' => $status,
      'Spielername' => $name,
      'Spielername_G' => '',
      'Geschlecht' => $gender,
      'Geburtsjahr' => 1990,
      'DWZ' => $dwz,
      'FIDE_Elo' => null,
      'FIDE_Titel' => $title,
      'FIDE_ID' => null,
      'FIDE_Land' => 'GER',
    ]);
  }

  private function getJson(string $uri): array {
    $this->client->request('GET', $uri);
    $this->assertResponseIsSuccessful();
    return json_decode($this->client->getResponse()->getContent(), true);
  }

  public function testPlayers_preferredZpsFirst() {
    $players = $this->getJson('/dwz/api/players/?name=Zzphpunit&preferredZps=X0002&active=1');

    $this->assertSame(['Zzphpunit, Bernd', 'Zzphpunit, Anna'], array_column($players, 'name'));
    $this->assertSame(['X0002', 'X0001'], array_column($players, 'zps'));

    $anna = $players[1];
    $this->assertSame('Zzphpunit Schachclub', $anna['club']);
    $this->assertSame('W', $anna['gender']);
    $this->assertSame('WFM', $anna['fideTitle']);
    $this->assertSame(2100, $anna['dwz']);
    $this->assertSame('https://www.schachbund.de/dwz-spieler.html?search=Zzphpunit%2CAnna', $anna['uri']);

    // Missing gender in the DSB database means male.
    $this->assertSame('M', $players[0]['gender']);
  }

  public function testPlayers_includesPassivePlayersWhenRequested() {
    $players = $this->getJson('/dwz/api/players/?name=Zzphpunit,%20Anna&preferredZps=X0002&active=0');

    $this->assertSame(['P', 'A'], array_column($players, 'status'));
    $this->assertSame(['X0002', 'X0001'], array_column($players, 'zps'));
  }

  public function testPlayers_missingName() {
    $this->client->request('GET', '/dwz/api/players/?preferredZps=X0002');
    $this->assertResponseStatusCodeSame(404);
  }

  public function testClubs() {
    $clubs = $this->getJson('/dwz/api/clubs/?name=Zzphpunit&zps=X');

    $this->assertSame([
      ['zps' => 'X0001', 'name' => 'Zzphpunit Schachclub'],
      ['zps' => 'X0002', 'name' => 'Zzphpunit Schachfreunde'],
    ], $clubs);
  }
}
