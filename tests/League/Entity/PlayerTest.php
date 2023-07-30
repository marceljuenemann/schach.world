<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Entity\Player;
use Nsv\League\Entity\Team;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
  public function testIsGuest_noZps() {
    $player = new Player();
    $this->assertFalse($player->isGuest());
  }

  public function testIsGuest_noTeamZps() {
    $player = new Player();
    $player->zps = '70156-0117';
    $player->team = new Team();
    $player->team->zps = null;

    $this->assertFalse($player->isGuest());
  }

  public function testIsGuest_zpsMatching() {
    $player = new Player();
    $player->zps = '70156-0117';
    $player->team = new Team();
    $player->team->zps = '70156';

    $this->assertFalse($player->isGuest());
  }

  public function testIsGuest_partnership() {
    $player = new Player();
    $player->zps = '70156-0117';
    $player->team = new Team();
    $player->team->zps = '7010170156';

    $this->assertFalse($player->isGuest());
  }

  public function testIsGuest_zpsNotMatching() {
    $player = new Player();
    $player->zps = '70156-0117';
    $player->team = new Team();
    $player->team->zps = '70101';

    $this->assertTrue($player->isGuest());
  }

}
