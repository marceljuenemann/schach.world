<?php

namespace Tests\League\Controller;

use Nsv\League\Core\Encoding;
use Tests\League\LeagueWebTestCase;

class MainControllerTest extends LeagueWebTestCase
{
  public function testOverview_withDate() {
    $response = $this->request('GET', '/ligen/nsv-2526/overview/?date=2025-11-16');

    $this->assertResponseIsSuccessful();
    $this->assertSame(Encoding::CHARSET, $response->getCharset());
    $this->assertHtmlContains('Landesliga Nord', $response);
    $this->assertHtmlContains('Fortuna Logabirum', $response);
  }

  public function testOverview_withoutDate() {
    $this->request('GET', '/ligen/nsv-2526/overview/');
    $this->assertResponseIsSuccessful();
  }

  public function testOverview_dateWithoutMatches() {
    $this->request('GET', '/ligen/nsv-2526/overview/?date=2000-01-01');
    $this->assertResponseIsSuccessful();
  }

  public function testOverview_invalidDate() {
    $this->request('GET', '/ligen/nsv-2526/overview/?date=yesterday');
    $this->assertResponseStatusCodeSame(404);
  }

  public function testOverview_noDates() {
    $response = $this->request('GET', '/ligen/fll-2526/overview/');
    $this->assertResponseIsSuccessful();
    $this->assertHtmlContains('Noch keine Spieltage hinterlegt.', $response);
  }

  public function testOverview_unknownLeague() {
    $response = $this->request('GET', '/ligen/does-not-exist/overview/');
    $this->assertResponseStatusCodeSame(404);
  }

  public function testTeam_public() {
    $response = $this->request('GET', '/ligen/nsv-2526/m/' . self::TEAM_ID . '/');

    $this->assertResponseIsSuccessful();
    $this->assertHtmlContains('Fortuna Logabirum', $response);
    // Player management dialogs are only rendered for managers.
    $this->assertStringNotContainsString('"preferredZps"', html_entity_decode($response->getContent()));
  }

  public function testTeam_asLeagueManager() {
    $this->login();
    $response = $this->request('GET', '/ligen/nsv-2526/m/' . self::TEAM_ID . '/');

    $this->assertResponseIsSuccessful();
    $html = html_entity_decode($response->getContent());
    $this->assertStringContainsString('"preferredZps":"70506"', $html);
    $this->assertStringContainsString('"roundCount":9', $html);
    $this->assertStringContainsString('"firstPlayerNumber"', $html);
  }

  public function testTeam_asManagerOfOtherDivision() {
    $league = $this->league();
    $this->login($league->divisionByPath('verbandsliga-nord'));
    $response = $this->request('GET', '/ligen/nsv-2526/m/' . self::TEAM_ID . '/');

    $this->assertResponseIsSuccessful();
    $this->assertStringNotContainsString('"preferredZps"', html_entity_decode($response->getContent()));
  }

  public function testTeam_unknownTeam() {
    $this->request('GET', '/ligen/nsv-2526/m/1/');
    $this->assertResponseStatusCodeSame(404);
  }

  public function testTeamApi_redactsContactDetails() {
    $response = $this->request('GET', '/ligen/nsv-2526/api/teams/' . self::TEAM_ID . '/');

    $this->assertResponseIsSuccessful();
    $data = json_decode($response->getContent(), true);
    $this->assertSame(self::TEAM_ID, $data['id']);
    $this->assertSame('** REDACTED **', $data['captain']['mail']);
    $this->assertSame('** REDACTED **', $data['captain']['phone']);
    $this->assertSame('** REDACTED **', $data['captain']['phone2']);
  }

  public function testTeamApi_asManagerShowsContactDetails() {
    $this->login();
    $response = $this->request('GET', '/ligen/nsv-2526/api/teams/' . self::TEAM_ID . '/');

    $this->assertResponseIsSuccessful();
    $data = json_decode($response->getContent(), true);
    $this->assertNotSame('** REDACTED **', $data['captain']['mail']);
  }

  public function testPlayer() {
    $response = $this->request('GET', '/ligen/nsv-2526/s/' . self::PLAYER_ID . '/');
    $this->assertResponseIsSuccessful();
    $this->assertHtmlContains('Player f189', $response);
  }

  public function testPlayerDebug() {
    $response = $this->request('GET', '/ligen/nsv-2526/s/' . self::PLAYER_ID . '/debug/');
    $this->assertResponseIsSuccessful();
    $this->assertResponseHeaderSame('Content-Type', 'text/plain; charset=' . Encoding::CHARSET);
    $this->assertHtmlContains('Player f189', $response);
  }
}
