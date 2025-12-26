<?php

namespace Nsv\League\Application;

use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Division;
use PHPUnit\Framework\Attributes\DataProvider;
use SED_Cache;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\League\LeagueTestCase;

class RankingTest extends LeagueTestCase
{
  use MatchesSnapshots;

  public function testNoTable() {
    $division = $this->division('pokal-2122', 'pokal-mm');
    $ranking = $this->legacyRanking($division, 3);
    $this->assertFalse($division->config('showRanking'));
    $this->assertThat($ranking, $this->equalTo([]));
  }

  public function testNoTeams() {
    $division = $this->division('pokal-2223', 'pokal-mm');
    $ranking = $this->legacyRanking($division, 3);
    $this->assertEmpty($division->teams());
    $this->assertThat($ranking, $this->equalTo([]));
  }

  public function testNoPairings() {
    $division = $this->division('sjbh-2021', 'bmm-u14');
    $ranking = $this->legacyRanking($division, 3);

    $this->assertNotEmpty($division->teams());
    $this->assertEmpty($division->pairings);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testRoundZero() {
    $division = $this->division('nsv-2526', 'landesliga-sued');
    $ranking = $this->legacyRanking($division, 0);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testMultiplePairingsPerRound() {
    $division = $this->division('sjbh-2526', 'bmm-u12');
    $ranking = $this->legacyRanking($division, 5);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testMultiplePairingsBetweenTeams() {
    $division = $this->division('bezirk6-2223', '2-kreisklasse-osnabrueck');
    $ranking = $this->legacyRanking($division, 6);
    $this->assertCount(2, $ranking[1][3]);
    $this->assertCount(2, $ranking[1][4]);
    $this->assertMatchesSnapshot($ranking);
  }

  /**
   * Tests
   * - tie break: win
   * - tie break: multiple teams
   * - berliner: equal
   * - berliner: different
   * - covid
   * - MP different (draw and win needed)
   * - Check for other special cases
   * - Test all divisions (specify division ID?)
   * - Check for coverage
   */

  /*
  #[DataProvider('rankingProvider')]
  public function testRanking(string $league, string $division, int $round): void {
    $league = $this->leagueRepository->findByPathOrPrefix($league);
    $division = $league->divisionByPath($division);
    $ranking = $this->legacyRanking($division, $round);
    Encoding::deep_utf8_encode($ranking);
    $this->assertMatchesSnapshot($ranking);
  }
  */

  private function legacyRanking(Division $div, int $round): array {
    $this->legacySystem->initialize();
    $this->legacySystem->league = $div->league;
    $this->legacySystem->division = $div;

    global $globals;
    $globals['bridge'] = $this->legacySystem;
    $_GET['r'] = $round;

    require('turnier-bootstrap.inc.php');  // NOTE: Not using require_once to reinitialize globals.
    require_once('turnier.inc.php');
    require_once('tabelle.inc.php');

    SED_Cache::clearAll();
    $ranking = Tabelle($div->id, $round, true);

    Encoding::deep_utf8_encode($ranking);
    return $ranking;  
  }

  private function division(string $leaguePath, string $divisionPath): Division {
    $league = $this->leagueRepository->findByPathOrPrefix($leaguePath);
    return $league->divisionByPath($divisionPath);
  }

  /*S
  public static function rankingProvider(): array {
    return [
      ['nsv-2526', 'landesliga-sued', 3],
      ['nsv-2526', 'landesliga-sued', 4],
      ['nsj-1819', 'jugendliga-niedersachsen', 5],
    ];
  }
  */
}
