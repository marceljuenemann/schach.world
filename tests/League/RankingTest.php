<?php

namespace Nsv\League\Application;

use Doctrine\ORM\EntityNotFoundException;
use Nsv\League\Api\Service\RankingService;
use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Division;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\League\LeagueTestCase;

class RankingTest extends LeagueTestCase
{
  use MatchesSnapshots;

  private RankingService $rankingService;

  protected function setUp(): void {
    parent::setUp();
    $this->rankingService = $this->container->get(RankingService::class);
  }

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
    $this->markTestSkipped('Not yet implemented.');
    $division = $this->division('bezirk6-2223', '2-kreisklasse-osnabrueck');
    $ranking = $this->legacyRanking($division, 6);
    $this->assertCount(2, $ranking[1][3]);
    $this->assertCount(2, $ranking[1][4]);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testDirectComparison_sameRank() {
    $division = $this->division('nsv-2526', 'landesliga-sued');
    $ranking = $this->legacyRanking($division, 2);
    $this->assertEquals("4.", $ranking[4][0]);
    $this->assertEquals("4.", $ranking[5][0]);
    $this->assertEquals("2", $ranking[4][12]);
    $this->assertEquals("2", $ranking[5][12]);
    $this->assertEquals("7½", $ranking[4][13]);
    $this->assertEquals("7½", $ranking[5][13]);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testDirectComparison_breaksTie() {
    $division = $this->division('nsv-2425', 'landesliga-nord');
    $ranking = $this->legacyRanking($division, 5);
    $this->assertEquals("6.", $ranking[6][0]);
    $this->assertEquals("7.", $ranking[7][0]);
    $this->assertEquals("4", $ranking[6][12]);
    $this->assertEquals("4", $ranking[7][12]);
    $this->assertEquals("20", $ranking[6][13]);
    $this->assertEquals("20", $ranking[7][13]);
    $this->assertEquals("4½", $ranking[6][8][0]["text"]);
    $this->assertEquals("3½", $ranking[7][7][0]["text"]);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testDirectComparison_multipleTeams() {
    // In this example, three teams have the same score. Two of them have
    // drawn against each other, so have 1 MP each. The tie between them
    // is broken by Berlin tie break.
    $division = $this->division('sjbh-2425', 'bmm-u12');
    $ranking = $this->legacyRanking($division, 2);
    $this->assertEquals("10.", $ranking[10][0]);
    $this->assertEquals("11.", $ranking[11][0]);
    $this->assertEquals("12.", $ranking[12][0]);
    $this->assertEquals("1", $ranking[10][18]);
    $this->assertEquals("1", $ranking[11][18]);
    $this->assertEquals("1", $ranking[12][18]);
    $this->assertEquals("4", $ranking[10][19]);
    $this->assertEquals("4", $ranking[11][19]);
    $this->assertEquals("4", $ranking[12][19]);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testBerlin_breaksTie() {
    $division = $this->division('nsv-2425', 'landesliga-nord');
    $ranking = $this->legacyRanking($division, 6);
    $this->assertEquals("3.", $ranking[3][0]);
    $this->assertEquals("4.", $ranking[4][0]);
    $this->assertEquals("8", $ranking[3][12]);
    $this->assertEquals("8", $ranking[4][12]);
    $this->assertEquals("28", $ranking[3][13]);
    $this->assertEquals("28", $ranking[4][13]);
    $this->assertEquals("4", $ranking[3][5][0]["text"]);
    $this->assertEquals("4", $ranking[4][4][0]["text"]);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testBerlin_sameRank() {
    // In this example, two teams played against each other, but
    // their Berlin score is the same, so they remain tied.
    $division = $this->division('sjbh-2425', 'bmm-u20');
    $ranking = $this->legacyRanking($division, 1);
    $this->assertEquals("5.", $ranking[5][0]);
    $this->assertEquals("5.", $ranking[6][0]);
    $this->assertEquals("1", $ranking[5][12]);
    $this->assertEquals("1", $ranking[6][12]);
    $this->assertEquals("2", $ranking[5][13]);
    $this->assertEquals("2", $ranking[6][13]);
    $this->assertEquals("2", $ranking[5][7][0]["text"]);
    $this->assertEquals("2", $ranking[6][6][0]["text"]);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testCovid_rules() {
    $this->markTestSkipped('Not yet implemented.');
    // In the COVID season, rounds were mixed up, so we should always
    // show the ranking with pairings for all rounds played.
    // TODO: Should be fine to remove this special case at this point.
    $division = $this->division('bezirk1-2122', 'bezirksliga');
    $ranking = $this->legacyRanking($division, 2);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testRelegation() {
    $division = $this->division('bezirk3-1920', 'kreisliga');
    $ranking = $this->legacyRanking($division, 11);
    $this->assertEquals("aufsteigerRelegation", $ranking[3][15]);
    $this->assertEquals("absteigerRelegation", $ranking[11][15]);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testMpCalculation_nsj_morePointsWins() {
    // Organisation "7j" is configured to give 2 MPs for a 2:1 result (4 boards).
    $division = $this->division('nsj-2425', 'landesklasse-sued-west');
    $ranking = $this->legacyRanking($division, 1);
    $this->assertEquals("2", $ranking[3][7][0]["text"]);
    $this->assertEquals("1", $ranking[6][4][0]["text"]);
    $this->assertEquals("2", $ranking[3][10]);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testMpCalculation_jbln_moreThanHalfRequired() {
    $this->markTestSkipped('Not yet implemented.');
    // Organisation "ndsj" is configured to give only 1 MP for a 3:2 result (6 boards).
    $division = $this->division('jbln-1718', 'staffel-ost');
    $ranking = $this->legacyRanking($division, 5);
    $this->assertEquals("3", $ranking[2][9][0]["text"]);
    $this->assertEquals("2", $ranking[8][3][0]["text"]);
    $this->assertEquals("7", $ranking[2][12]);
    $this->assertMatchesSnapshot($ranking);
  }

  public function testAllDivisions() {
    // TODO: Probably remove this test altogether after ranking is rewritten.
    $this->markTestSkipped('This is an expensive test.');
    $divisions = $this->em->getRepository(Division::class)->findBy([], ['id' => 'ASC']);
    foreach ($divisions as $division) {
      try {
        if (!$division->league->id) continue;
      } catch (EntityNotFoundException $e) {
        continue;
      }
      $ranking = $this->legacyRanking($division, 99);
      $this->assertMatchesSnapshot($ranking);
    }
  }

  private function legacyRanking(Division $div, int $round): array {
    $ranking = $this->rankingService->ranking($div, $round);
    $legacy = $ranking->toLegacyFormat($div);
    return Encoding::deep_utf8_encode($legacy);
  }

  /*
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

    return Encoding::deep_utf8_encode($ranking);
  }
  */

  private function division(string $leaguePath, string $divisionPath): Division {
    $league = $this->leagueRepository->findByPathOrPrefix($leaguePath);
    return $league->divisionByPath($divisionPath);
  }
}
