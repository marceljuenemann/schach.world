<?php

namespace Tests\League\Controller;

use Tests\League\LeagueWebTestCase;

class RootControllerTest extends LeagueWebTestCase
{
  private const PAIRING_ID = 38668;

  public function testRoot() {
    $this->request('GET', '/ligen/');
    $this->assertResponseRedirects('/alle-ligen/');
  }

  public function testResultEdit() {
    $param = $this->encodeId(self::PAIRING_ID) . 'token123';
    $this->request('GET', "/ligen/1/$param");
    $this->assertResponseRedirects('/ligen/nsv-2526/?m=eingabe&pid=' . self::PAIRING_ID . '&auth=token123');
  }

  public function testTeamEdit() {
    $param = $this->encodeId(self::TEAM_ID) . 'token123';
    $this->request('GET', "/ligen/2/$param");
    $this->assertResponseRedirects('/ligen/nsv-2526/m/' . self::TEAM_ID . '/?auth=token123');
  }

  public function testDivisionLogin() {
    $this->request('GET', '/ligen/3/' . self::DIVISION_ID);
    $this->assertResponseRedirects('/ligen/nsv-2526/?m=startseite&staffel=' . self::DIVISION_ID);
  }

  /** Encodes an ID the way the legacy system does in short links. */
  private function encodeId(int $id): string {
    return str_pad(base_convert((string) $id, 10, 36), 4, '0', STR_PAD_LEFT);
  }
}
