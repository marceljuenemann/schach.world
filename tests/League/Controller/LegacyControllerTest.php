<?php

namespace Tests\League\Controller;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Tests\League\LeagueWebTestCase;
use Tests\League\TemporaryLeague;

/**
 * The legacy system determines the module to run in a require_once'd file and keeps state in
 * global variables, so each request needs to be run in a fresh PHP process.
 */
#[RunTestsInSeparateProcesses]
class LegacyControllerTest extends LeagueWebTestCase
{
  private ?TemporaryLeague $tmp = null;

  protected function tearDown(): void {
    $this->tmp?->cleanup();
    parent::tearDown();
  }

  /**
   * On some error paths, LegacyController closes an output buffer it didn't open. In production,
   * WordPress provides an outer buffer; emulate that so PHPUnit's own buffer stays intact.
   */
  protected function request(string $method, string $uri, array $parameters = [], mixed $json = null, array $headers = []): Response {
    $level = ob_get_level();
    ob_start();
    try {
      return parent::request($method, $uri, $parameters, $json, $headers);
    } finally {
      while (ob_get_level() > $level) {
        ob_end_clean();
      }
    }
  }

  private function temporaryLeague(): TemporaryLeague {
    return $this->tmp = new TemporaryLeague(static::getContainer()->get('doctrine.dbal.league_connection'));
  }

  public function testStartseite_forwardsToOverview() {
    $response = $this->request('GET', '/ligen/nsv-2526/?m=startseite&date=2025-11-16');
    $this->assertResponseIsSuccessful();
    $this->assertHtmlContains('Fortuna Logabirum', $response);
  }

  public function testDefaultModule_isStartseite() {
    $this->request('GET', '/ligen/nsv-2526/');
    $this->assertResponseIsSuccessful();
  }

  public function testSpielplan_redirects() {
    $this->request('GET', '/ligen/nsv-2526/?staffel=' . self::DIVISION_ID);
    $this->assertResponseRedirects('/ligen/nsv-2526/landesliga-nord/spielplan/');
  }

  public function testStatistik_redirects() {
    $this->request('GET', '/ligen/nsv-2526/?staffel=' . self::DIVISION_ID . '&r=statistik');
    $this->assertResponseRedirects('/ligen/nsv-2526/landesliga-nord/statistik');
  }

  public function testSpieltag_redirectsToMatchday() {
    $this->request('GET', '/ligen/nsv-2526/?staffel=' . self::DIVISION_ID . '&r=3');
    $this->assertResponseRedirects('/ligen/nsv-2526/landesliga-nord/3/');
  }

  public function testSpieltag_withoutRound_redirectsToDivision() {
    $this->request('GET', '/ligen/nsv-2526/?staffel=' . self::DIVISION_ID . '&r=');
    $this->assertResponseRedirects('/ligen/nsv-2526/landesliga-nord/');
  }

  public function testSpieltag_pdf_redirects() {
    $this->request('GET', '/ligen/nsv-2526/?staffel=' . self::DIVISION_ID . '&r=3&ausgabe=pdf');
    $this->assertResponseRedirects('/ligen/nsv-2526/landesliga-nord/3/pdf/');
  }

  public function testMannschaft_redirects() {
    $this->request('GET', '/ligen/nsv-2526/?mannschaft=' . self::TEAM_ID);
    $this->assertResponseRedirects('/ligen/nsv-2526/m/' . self::TEAM_ID . '/');
  }

  public function testSpieler_redirects() {
    $this->request('GET', '/ligen/nsv-2526/?spieler=' . self::PLAYER_ID);
    $this->assertResponseRedirects('/ligen/nsv-2526/s/' . self::PLAYER_ID . '/');
  }

  public function testMannschaft_unknownTeam() {
    $this->request('GET', '/ligen/nsv-2526/?mannschaft=1');
    $this->assertResponseStatusCodeSame(404);
  }

  public function testAdminLogin_success() {
    $tmp = $this->temporaryLeague();
    $this->request('POST', "/ligen/{$tmp->path}/?admin=login", [
      'benutzer' => 't-' . $tmp->id,
      'passwort' => TemporaryLeague::MANAGER_PASSWORD,
    ]);
    $this->assertResponseRedirects("/ligen/{$tmp->path}/?admin=desktop--");
  }

  public function testAdminLogin_divisionManager() {
    $tmp = $this->temporaryLeague();
    $this->request('POST', "/ligen/{$tmp->path}/?admin=login", [
      'benutzer' => 's-' . $tmp->divisionIds[0],
      'passwort' => TemporaryLeague::MANAGER_PASSWORD,
    ]);
    $this->assertResponseRedirects("/ligen/{$tmp->path}/?admin=desktop--");
  }

  public function testAdminLogin_wrongPassword() {
    $tmp = $this->temporaryLeague();
    $response = $this->request('POST', "/ligen/{$tmp->path}/?admin=login", [
      'benutzer' => 't-' . $tmp->id,
      'passwort' => 'wrong',
    ]);
    $this->assertResponseStatusCodeSame(403);
    $this->assertHtmlContains('Fehler 403: Zugriff nicht erlaubt oder abgelaufen (Falsches Passwort)', $response);
  }

  public function testAdminLogout() {
    $this->login();
    $this->request('GET', '/ligen/nsv-2526/?admin=logout-abc');
    $this->assertResponseRedirects('/ligen/nsv-2526/');
  }

  public function testAdminDesktop_requiresLogin() {
    $response = $this->request('GET', '/ligen/nsv-2526/?admin=desktop--');
    $this->assertResponseStatusCodeSame(403);
    $this->assertHtmlContains('Kein session cookie vorhanden', $response);
  }

  public function testAdminDesktop_asLeagueManager() {
    $this->login();
    $response = $this->request('GET', '/ligen/nsv-2526/?admin=desktop--');
    $this->assertResponseIsSuccessful();
    $this->assertStringNotContainsString('Leider ist ein Fehler aufgetreten', $response->getContent());
    $this->assertHtmlContains('Landesliga Nord', $response);
  }

  public function testAdminDesktop_asDivisionManager() {
    $this->login($this->league()->divisionById(self::DIVISION_ID));
    $response = $this->request('GET', '/ligen/nsv-2526/?admin=desktop--');
    $this->assertResponseIsSuccessful();
    $this->assertStringNotContainsString('Leider ist ein Fehler aufgetreten', $response->getContent());
  }
}
