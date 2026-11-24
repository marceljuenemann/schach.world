<?php

namespace Tests\League\Controller;

use Nsv\League\Core\Encoding;
use Tests\League\LeagueWebTestCase;

class DivisionControllerTest extends LeagueWebTestCase
{
  private const BASE = '/ligen/nsv-2526/landesliga-nord/';

  public function testIndex() {
    $response = $this->request('GET', self::BASE);
    $this->assertResponseIsSuccessful();
    $this->assertHtmlContains('Landesliga Nord', $response);
  }

  public function testIndex_divisionWithoutDates() {
    $this->request('GET', '/ligen/frl-2526/nord/');
    $this->assertResponseIsSuccessful();
  }

  public function testIndex_unknownDivision() {
    $response = $this->request('GET', '/ligen/nsv-2526/does-not-exist/');
    $this->assertResponseStatusCodeSame(404);
    // Errors are rendered with the league template by ControllerInterceptor.
    $this->assertHtmlContains('Fehler 404: Seite nicht gefunden', $response);
  }

  public function testMatchday() {
    $response = $this->request('GET', self::BASE . '3/');
    $this->assertResponseIsSuccessful();
    $this->assertHtmlContains('Fortuna Logabirum', $response);
    $this->assertHtmlContains('/ligen/nsv-2526/landesliga-nord/spielplan/', $response);
    $this->assertHtmlContains('/ligen/nsv-2526/landesliga-nord/statistik', $response);
  }

  public function testSchedule() {
    $response = $this->request('GET', self::BASE . 'spielplan/');
    $this->assertResponseIsSuccessful();
    $this->assertHtmlContains('Hagener SV', $response);
  }

  public function testScheduleDebug() {
    $response = $this->request('GET', self::BASE . 'spielplan/debug/');
    $this->assertResponseIsSuccessful();
    $this->assertResponseHeaderSame('Content-Type', 'text/plain; charset=' . Encoding::CHARSET);
    $this->assertHtmlContains('Fortuna Logabirum', $response);
  }

  public function testStatistics() {
    $response = $this->request('GET', self::BASE . 'statistik');
    $this->assertResponseIsSuccessful();
    $this->assertHtmlContains('Landesliga Nord', $response);
    $this->assertHtmlContains('Fortuna Logabirum', $response);
  }

  public function testStatistics_noGamesPlayed() {
    $response = $this->request('GET', '/ligen/frl-2526/nord/statistik');
    $this->assertResponseIsSuccessful();
    $this->assertHtmlContains('Nord', $response);
  }

  public function testMatchdayPdf() {
    $response = $this->request('GET', self::BASE . '1/pdf/');
    $this->assertResponseIsSuccessful();
    $this->assertStringStartsWith('%PDF', $response->getContent());
  }

  public function testPgn() {
    $response = $this->request('GET', self::BASE . '1/pgn/');
    $this->assertResponseIsSuccessful();
    $this->assertResponseHeaderSame('Content-Disposition', 'attachment; filename="landesliga-nord-R1.pgn"');
    $this->assertResponseHeaderSame('Content-Type', 'application/x-chess-pgn; charset=UTF-8');
    $this->assertStringContainsString('[Event "Landes- und Verbandsligen 2025/26 - Landesliga Nord"]', $response->getContent());
  }

  public function testMatchdayApi() {
    $response = $this->request('GET', '/ligen/nsv-2526/api/divisions/landesliga-nord/rounds/2/');
    $this->assertResponseIsSuccessful();
    $data = json_decode($response->getContent(), true);
    $this->assertSame(2, $data['round']);
    $this->assertCount(5, $data['pairings']);
  }

  public function testCurrentMatchdayApi() {
    $response = $this->request('GET', '/ligen/nsv-2526/api/divisions/landesliga-nord/rounds/current/');
    $this->assertResponseIsSuccessful();
    $data = json_decode($response->getContent(), true);
    $this->assertArrayHasKey('pairings', $data);
  }

  public function testCurrentMatchdayApi_divisionWithoutDates() {
    $response = $this->request('GET', '/ligen/frl-2526/api/divisions/nord/rounds/current/');
    $this->assertResponseIsSuccessful();
    $data = json_decode($response->getContent(), true);
    $this->assertSame(1, $data['round']);
  }
}
