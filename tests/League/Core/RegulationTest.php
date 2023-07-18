<?php

use Nsv\League\Core\Regulation;
use Nsv\League\Entity\League;
use PHPUnit\Framework\TestCase;

final class RegulationTest extends TestCase 
{
  public function testIsWhiteGame() {
    $league = new League();
    $league->organisation = '7';

    // Home team.
    $this->assertSame(false, Regulation::isWhiteGame(true, 1, $league));
    $this->assertSame(true, Regulation::isWhiteGame(true, 2, $league));
    $this->assertSame(false, Regulation::isWhiteGame(true, 3, $league));
    $this->assertSame(true, Regulation::isWhiteGame(true, 4, $league));

    // Guest team.
    $this->assertSame(true, Regulation::isWhiteGame(false, 1, $league));
    $this->assertSame(false, Regulation::isWhiteGame(false, 2, $league));
    $this->assertSame(true, Regulation::isWhiteGame(false, 3, $league));
    $this->assertSame(false, Regulation::isWhiteGame(false, 4, $league));
  }

  public function testIsWhiteGame_nsvPokal() {
    $league = new League();
    $league->organisation = '7p';

    // Home team.
    $this->assertSame(false, Regulation::isWhiteGame(true, 1, $league));
    $this->assertSame(true, Regulation::isWhiteGame(true, 2, $league));
    $this->assertSame(true, Regulation::isWhiteGame(true, 3, $league));
    $this->assertSame(false, Regulation::isWhiteGame(true, 4, $league));

    // Guest team.
    $this->assertSame(true, Regulation::isWhiteGame(false, 1, $league));
    $this->assertSame(false, Regulation::isWhiteGame(false, 2, $league));
    $this->assertSame(false, Regulation::isWhiteGame(false, 3, $league));
    $this->assertSame(true, Regulation::isWhiteGame(false, 4, $league));
  }

}
