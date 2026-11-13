<?php

namespace Nsv\Dwz\Calculation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the DWZ calculation against the official DSB Wertungsordnung, valid from 2026-06-08
 * (https://www.schachbund.de/wertungsordnung.html), in particular its example tournament.
 */
class DwzCalculatorTest extends TestCase
{
  /** The tournament in the Wertungsordnung example ends in 2026. */
  const YEAR = 2026;

  private DwzCalculator $calculator;

  protected function setUp(): void {
    $this->calculator = new DwzCalculator();
  }

  /**
   * The established players A-H from the example tournament in Anhang 3.1 of the Wertungsordnung.
   *
   * Opponent ratings are taken from Anhang 3.1.8 (i.e. with first ratings R1 of the previously unrated
   * opponents and with the "Sonderwertung" for player G already applied). K and We are from Anhang 3.1.9,
   * Rh from 3.1.7 and the new DWZ from 3.1.11 (= R0 + ΔR from 3.1.10; the Rn column of 3.1.10 itself
   * contains several outdated values).
   *
   * Note: In Anhang 3.1.8, D's opponent H is listed with 1200, but the P(D) value and the tournament table
   * show it's a typo for H's DWZ of 1300.
   */
  public static function officialExample(): array {
    return [
      //       age, R0,   index, opponents,                                                   W,   We,    K,    Rh,   Rn
      'A' => [40, 2220, 31, [2100, 2000, 1950, 1800, 1700, 1961, 1476],                       7.0, 5.990, 25.0, 2331, 2245],
      'B' => [22, 2100, 55, [2220, 1800, 1961, 1179, 2329],                                   2.5, 3.088, 32.0, 1857, 2081],
      'C' => [55, 2000, 25, [2220, 1950, 2329, 1494],                                         1.0, 1.873, 25.0, null, 1978],
      'D' => [16, 1950, 15, [2220, 2000, 1800, 1961, 1300, 1476, 2329],                       4.5, 3.818, 33.7, 2007, 1973],
      'E' => [20, 1800,  6, [2220, 2100, 1950, 1700, 1961, 1300, 1476, 2329, 1494],           3.0, 4.160, 42.0, 1580, 1751],
      'F' => [43, 1700,  8, [2220, 1800, 1961, 1300, 1179, 2329, 1494],                       3.0, 3.241, 34.0, 1574, 1692],
      'G' => [24, 1640,  3, [2220, 2100, 1950, 1800, 1700, 1476],                             3.5, 1.630, 48.0, 1961, 1730],
      'H' => [66, 1300, 77, [1950, 1700, 1494, 1179, 1476, 1800],                             1.0, 1.308, 24.5, 1227, 1292],
    ];
  }

  #[DataProvider('officialExample')]
  public function testOfficialExample(int $age, int $dwz, int $index, array $opponents, float $points,
      float $we, float $k, ?int $performance, int $newDwz) {
    $result = $this->calculator->calculate($dwz, $opponents, $points, self::YEAR - $age, $index, self::YEAR);

    $this->assertSame(count($opponents), $result->games);
    $this->assertSame($dwz, $result->oldDwz);
    $this->assertSame($points, $result->points);
    // The Wertungsordnung sums P(D) values rounded to 3 decimals, we calculate exactly.
    $this->assertEqualsWithDelta($we, $result->expectedScore, 0.002, 'Erwartung');
    $this->assertEquals($k, $result->developmentFactor, 'K');
    $this->assertSame($newDwz, $result->newDwz, 'Neue DWZ');
    $this->assertSame($newDwz - $dwz, $result->difference());
    if ($performance) {
      // The performance Rh is calculated before the "Sonderwertung", i.e. with G's original DWZ 1640
      // instead of his performance 1961 (see Anhang 3.1.6).
      $rhOpponents = array_map(fn($rating) => $rating == 1961 ? 1640 : $rating, $opponents);
      $result = $this->calculator->calculate($dwz, $rhOpponents, $points, self::YEAR - $age, $index, self::YEAR);
      // Rounded table values for D(P) vs. exact calculation.
      $this->assertEqualsWithDelta($performance, $result->performance, 2, 'Leistung');
    } else {
      $this->assertNull($result->performance);
    }
  }

  // Rule: For 100%, a fictitious draw against the opponents' average is added for the performance (3.6.2).
  public function testPerformanceWithFullScore() {
    // 5.5/6 against 1800: D(0.917) = 390 (Anhang 2.2).
    $result = $this->calculator->calculate(1800, array_fill(0, 5, 1800), 5.0, 1980, 6, self::YEAR);
    $this->assertEqualsWithDelta(2190, $result->performance, 2);
    $this->assertSame(5, $result->games);
    $this->assertSame(5.0, $result->points);
    // The fictitious draw only counts for the performance: K = 38, We = 2.5.
    $this->assertSame(1800 + (int) round(38 * 2.5), $result->newDwz);
  }

  // Values from the probability table (Anhang 2.1) and the example (Anhang 3.1.8).
  public static function probabilities(): array {
    return [[0, 0.5], [100, 0.638], [-100, 0.362], [200, 0.760], [267, 0.827], [120, 0.664],
        [-229, 0.209], [300, 0.856], [921, 0.999], [-650, 0.011], [744, 0.996]];
  }

  #[DataProvider('probabilities')]
  public function testProbability(int $difference, float $probability) {
    $this->assertSame($probability, round(DwzCalculator::probability($difference), 3));
  }

  // The iteration results Ri and corrected first ratings R1 of J-N from the example (Anhang 3.1.4),
  // plus the special cases of 3.4.4.2.
  public static function firstRatingCorrections(): array {
    return [[1430, 1476], [1108, 1179], [2329, 2329], [1387, 1436], [1450, 1494],
        [2000, 2000], [1100, 1172], [1000, 1165], [0, 1100], [-50, 1100]];
  }

  #[DataProvider('firstRatingCorrections')]
  public function testCorrectFirstRating(int $ri, int $r1) {
    $this->assertSame($r1, DwzCalculator::correctFirstRating($ri));
  }

  public function testResult() {
    $result = $this->calculator->calculate(1800, [1700, 1800, 1900, 2000, 1600], 3.0, 1980, 6, self::YEAR);
    $this->assertSame(5, $result->games);
    $this->assertSame(3.0, $result->points);
    $this->assertSame(1800, $result->oldDwz);
    $this->assertSame(1800, $result->opponentAverage);
    $this->assertEqualsWithDelta(2.5, $result->expectedScore, 0.001);
    $this->assertEquals(38, $result->developmentFactor);
    $this->assertNotNull($result->performance);
    $this->assertSame(1800 + 19, $result->newDwz);
    $this->assertSame(19, $result->difference());
    $this->assertNull($result->unavailableReason);
  }

  public function testNoRatedGames() {
    foreach ([1800, null] as $dwz) {
      $result = $this->calculator->calculate($dwz, [null, 0], 0.0, 1980);
      $this->assertEquals(new DwzCalculation(0, 0.0, $dwz, null, unavailableReason: DwzUnavailableReason::NoRatedGames), $result);
      $this->assertNull($result->difference());
    }
  }

  public function testUnratedOpponentsAreIgnored() {
    $withUnrated = $this->calculator->calculate(1800, [1700, 0, null, 1900], 1.5, 1980, 6, self::YEAR);
    $without = $this->calculator->calculate(1800, [1700, 1900], 1.5, 1980, 6, self::YEAR);
    $this->assertSame(2, $withUnrated->games);
    $this->assertEquals($without, $withUnrated);
  }

  public function testInvalidScore() {
    $this->expectException(\InvalidArgumentException::class);
    $this->calculator->calculate(1800, [1700, 1900], 2.5, 1980);
  }

  // Rule: Rn = R0 + K * (W - We), with a lower limit of 1100 (3.5.2).
  public function testLowerLimit() {
    $result = $this->calculator->calculate(1120, array_fill(0, 5, 1300), 0.0, 1970, 6, self::YEAR);
    $this->assertSame(1100, $result->newDwz);
    $this->assertSame(-20, $result->difference());
  }

  // Rule: Brake factor b = (R0 + 800) / 2400 if W < We and R0 < 1600, independent of age (Anhang 1.3).
  public function testBrakeFactor() {
    // Adult, index 6: K0 = 38. b = 2000 / 2400.
    $result = $this->calculator->calculate(1200, [1200, 1200], 0.0, 1970, 6, self::YEAR);
    $this->assertEquals(31.7, $result->developmentFactor);
    $this->assertSame(1200 - 32, $result->newDwz);

    // No brake if W >= We.
    $result = $this->calculator->calculate(1200, [1200, 1200], 1.0, 1970, 6, self::YEAR);
    $this->assertEquals(38 + 4, $result->developmentFactor);

    // No brake from 1600.
    $result = $this->calculator->calculate(1600, [1600, 1600], 0.0, 1970, 6, self::YEAR);
    $this->assertEquals(38, $result->developmentFactor);
  }

  // Rule: Success bonus a if W >= We: (2000 - R0) / 30 for age <= 20 and R0 < 2000,
  // 4 for age >= 21 and R0 < 1600 (Anhang 1.3).
  public function testSuccessBonusBoundaries() {
    // W = 2 >= We = 1 against two equally rated opponents. Index 6, so K0 is 42 (junior) or 38 (adult).
    $k = fn(int $dwz, int $age, int $index = 6) => $this->calculator->calculate(
        $dwz, [$dwz, $dwz], 2.0, self::YEAR - $age, $index, self::YEAR)->developmentFactor;
    $this->assertEquals(42 + 10, $k(1700, 20));     // age 20: youth bonus
    $this->assertEquals(42, $k(1700, 21));          // age 21, R0 >= 1600: no bonus, still junior K0
    $this->assertEquals(42 + 4, $k(1500, 21));      // age 21, R0 < 1600: a1
    $this->assertEquals(38 + 4, $k(1500, 26));      // adult, R0 < 1600: a2
    $this->assertEquals(42, $k(2000, 18));          // youth from 2000: no bonus
    $this->assertEquals(72, $k(1100, 10));          // 42 + 900 / 30
    $this->assertEquals(80, $k(1100, 10, 1));       // 60 + 30, capped at Kmax
  }

  // Rule: K0 table (Anhang 1.3).
  public static function k0Table(): array {
    return [
      //                 age, dwz,  index, K0
      'youth idx 1'   => [25, 2100, 1, 60],
      'youth idx 3'   => [25, 2100, 3, 48],
      'youth idx 10'  => [25, 2100, 10, 34],
      'youth idx >10' => [25, 2100, 11, 32],
      'youth 2200'    => [25, 2200, 6, 35],
      'adult idx 3'   => [26, 1999, 3, 44],
      'adult idx >10' => [26, 1999, 99, 28],
      'adult 2000'    => [26, 2000, 6, 35],
      'adult 2000 >10' => [26, 2000, 12, 25],
    ];
  }

  #[DataProvider('k0Table')]
  public function testK0Table(int $age, int $dwz, int $index, int $k0) {
    // A slight underperformance (no bonus) at a DWZ >= 1600 (no brake) yields K = K0.
    $result = $this->calculator->calculate($dwz, [$dwz, $dwz], 0.5, self::YEAR - $age, $index, self::YEAR);
    $this->assertEquals($k0, $result->developmentFactor);
  }

  public function testAgeUsesEvaluationYear() {
    // Born 2005: 20 in 2025 (youth bonus), 21 in 2026 (none).
    $in2025 = $this->calculator->calculate(1700, [1700, 1700], 2.0, 2005, 6, 2025);
    $in2026 = $this->calculator->calculate(1700, [1700, 1700], 2.0, 2005, 6, 2026);
    $this->assertEquals(52, $in2025->developmentFactor);
    $this->assertEquals(42, $in2026->developmentFactor);
  }

  public function testDefaultIndexIsMoreThanTen() {
    $result = $this->calculator->calculate(1700, [1700, 1700], 0.5, 1970, evaluationYear: self::YEAR);
    $this->assertEquals(28, $result->developmentFactor);
  }

  public function testUnknownYearOfBirthCountsAsAdult() {
    $result = $this->calculator->calculate(1700, [1700, 1700], 0.5, null, 6, self::YEAR);
    $this->assertEquals(38, $result->developmentFactor);
  }

  // Rule: Leistung only for at least 5 games, and not for 0% (3.6).
  public function testPerformance() {
    $this->assertNull($this->calculator->calculate(1800, array_fill(0, 4, 1800), 2.0, 1980)->performance);
    $this->assertNull($this->calculator->calculate(1800, array_fill(0, 5, 1800), 0.0, 1980)->performance);
    $this->assertSame(1800, $this->calculator->calculate(1800, array_fill(0, 5, 1800), 2.5, 1980)->performance);
  }

  // Rule: First rating needs at least 5 games against rated opponents and at least 0.5 points (3.4.1.1).
  public function testFirstRatingRequirements() {
    $this->assertEquals(
        new DwzCalculation(4, 2.0, null, 1500, unavailableReason: DwzUnavailableReason::TooFewGamesForFirstRating),
        $this->calculator->calculate(null, array_fill(0, 4, 1500), 2.0, 1980));
    $this->assertEquals(
        new DwzCalculation(5, 0.0, null, 1500, unavailableReason: DwzUnavailableReason::NoPointsForFirstRating),
        $this->calculator->calculate(0, array_fill(0, 5, 1500), 0.0, 1980));
    $result = $this->calculator->calculate(null, array_fill(0, 5, 1500), 0.5, 1980);
    $this->assertNotNull($result->newDwz);
    $this->assertNull($result->unavailableReason);
  }

  // Rule: First rating R1 from the iteration result Ri (Anhang 1.2.2).
  public function testFirstRating() {
    // 50% against 1500: Ri = 1500, R1 = 1500 + 0.08 * 500 = 1540.
    $result = $this->calculator->calculate(null, array_fill(0, 6, 1500), 3.0, 1980, 6, self::YEAR);
    $this->assertEquals(new DwzCalculation(6, 3.0, null, 1500, newDwz: 1540), $result);

    // 50% against 2100: R1 = Ri = 2100.
    $result = $this->calculator->calculate(null, array_fill(0, 6, 2100), 3.0, 1980, 6, self::YEAR);
    $this->assertSame(2100, $result->newDwz);

    // 50% against 1000: R1 = 1100 + 9 * 0.08 * 1000 / 11 = 1165.45
    $result = $this->calculator->calculate(null, array_fill(0, 6, 1000), 3.0, 1980, 6, self::YEAR);
    $this->assertEqualsWithDelta(1165, $result->newDwz, 1);
  }

  // The iteration has to happen even if the starting value Rc + D(50%) = Rc is the average, as the
  // opponents' ratings might not be symmetric. Rated player B from the example has Rh = 1857.
  public function testIterationWithFiftyPercent() {
    $opponents = [2220, 1800, 1640, 1179, 2329];
    $this->assertSame(1834, $this->calculator->calculate(2100, $opponents, 2.5, 2004, 55, self::YEAR)->opponentAverage);
    $this->assertEqualsWithDelta(1857, $this->calculator->calculate(2100, $opponents, 2.5, 2004, 55, self::YEAR)->performance, 1);
    // Ri = 1858, R1 = 1858 + 0.08 * 142 = 1869.36
    $this->assertSame(1869, $this->calculator->calculate(null, $opponents, 2.5, 2004)->newDwz);
  }

  // Rule: For 100%, a fictitious draw against the opponents' average is added (3.4.1.3).
  public function testFirstRatingWithFullScore() {
    // 5/5 + draw vs. 1500 = 5.5/6 against 1500: D(0.917) = 390, R1 = 1890 + 0.08 * 110 = 1898.8.
    $result = $this->calculator->calculate(null, array_fill(0, 5, 1500), 5.0, 1980, 6, self::YEAR);
    $this->assertSame(5, $result->games);
    $this->assertSame(5.0, $result->points);
    $this->assertEqualsWithDelta(1899, $result->newDwz, 2);
  }

  /**
   * Generates random but plausible inputs with a fixed seed.
   */
  public static function randomInputs(): iterable {
    mt_srand(4711);
    for ($i = 0; $i < 300; $i++) {
      $n = mt_rand(1, 11);
      $center = mt_rand(1100, 2500);
      $spread = mt_rand(0, 1) ? 150 : 600;
      $opponents = [];
      for ($j = 0; $j < $n; $j++) {
        $opponents[] = max(1100, min(2800, $center + mt_rand(-$spread, $spread)));
      }
      $dwz = mt_rand(0, 3) ? mt_rand(1100, 2600) : null;
      $yearOfBirth = mt_rand(0, 5) ? mt_rand(1940, 2018) : null;
      yield "case $i" => [$dwz, $opponents, mt_rand(0, 2 * $n) / 2, $yearOfBirth, mt_rand(1, 40)];
    }
  }

  #[DataProvider('randomInputs')]
  public function testProperties(?int $dwz, array $opponents, float $points, ?int $yearOfBirth, int $index) {
    $calc = fn(float $points) => $this->calculator->calculate($dwz, $opponents, $points, $yearOfBirth, $index, self::YEAR);
    $result = $calc($points);
    $n = count($opponents);

    if (!$dwz && ($n < 5 || $points == 0)) {
      $this->assertNull($result->newDwz);
      $this->assertNotNull($result->unavailableReason);
      return;
    }
    $this->assertNull($result->unavailableReason);
    $this->assertSame($n, $result->games);
    $this->assertGreaterThanOrEqual(1100, $result->newDwz);
    $this->assertSame($result->performance !== null, $dwz && $n >= 5 && $points > 0);
    if ($dwz) {
      $this->assertSame($result->newDwz - $dwz, $result->difference());
      $this->assertGreaterThanOrEqual(0, $result->expectedScore);
      $this->assertLessThanOrEqual($n, $result->expectedScore);
      $this->assertLessThanOrEqual(80, $result->developmentFactor);
      // Higher score means positive change, lower score negative change.
      if ($points > $result->expectedScore + 0.01) $this->assertGreaterThanOrEqual(0, $result->difference());
      if ($points < $result->expectedScore - 0.01) $this->assertLessThanOrEqual(0, $result->difference());
    }

    // Monotonic: half a point more never yields a lower DWZ.
    if ($points + 0.5 <= $n) {
      $this->assertGreaterThanOrEqual($result->newDwz, $calc($points + 0.5)->newDwz);
    }
  }
}
