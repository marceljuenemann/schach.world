<?php

namespace Nsv\Dwz\Calculation;

/**
 * Performs an inofficial DWZ rating calculation for a single player according to the
 * DSB Wertungsordnung valid from 2026-06-08 (https://www.schachbund.de/wertungsordnung.html).
 * Section numbers in comments refer to that document.
 *
 * Like any calculation from the perspective of a single player, this is an approximation
 * (see Anhang 3.3): It ignores unrated opponents (even if they obtain a first rating in the same
 * tournament), the "Sonderwertung" (3.6.3), and "Restpartien" of unrated players (3.4.2).
 */
class DwzCalculator
{
  /** Unrated players need at least this many games against rated opponents for a first rating (3.4.1.1). */
  const MIN_GAMES_FIRST_RATING = 5;

  /** Established players need at least this many games for a performance rating (3.6.1). */
  const MIN_GAMES_PERFORMANCE = 5;

  /**
   * The DWZ index is not available to us. Most league players have played more than 10 rated
   * tournaments, so we assume an experienced player.
   */
  const DEFAULT_INDEX = 11;

  /** Lower limit for any DWZ (3.4.4.2, 3.5.2). */
  const MIN_DWZ = 1100;

  /** Default values of the control variables (Anhang 1.2.1). */
  const R = 0.08;
  const T = 30;
  const U = 800;
  const A1 = 4;
  const A2 = 4;
  const K_MAX = 80;

  /**
   * Base value K0 of the development factor by index 1 to 10 and >10 (Anhang 1.3), for
   * juniors up to 25 years below DWZ 2200, adults below DWZ 2000, and everybody else.
   */
  const K0_JUNIOR = [60, 60, 48, 46, 44, 42, 40, 38, 36, 34, 32];
  const K0_ADULT = [60, 60, 44, 42, 40, 38, 36, 34, 32, 30, 28];
  const K0_STRONG = [60, 60, 41, 39, 37, 35, 33, 31, 29, 27, 25];

  /**
   * @param int|null $dwz current DWZ, or null/0 if the player is unrated
   * @param array $opponentDwz DWZ of the opponents. Unrated opponents (null/0) are ignored.
   * @param float $points the score in games against rated opponents
   * @param int|null $yearOfBirth YOB of the player, null if unknown (treated as adult)
   * @param int $index DWZ index of the player
   * @param int|null $evaluationYear the year in which the tournament ends, which determines the
   *     player's age. Defaults to the current year.
   * @return DwzCalculation the result, which explains why if no new DWZ could be calculated.
   */
  function calculate(int|null $dwz, array $opponentDwz, float $points, int|null $yearOfBirth,
      int $index = self::DEFAULT_INDEX, int|null $evaluationYear = null): DwzCalculation {
    $dwz = $dwz ?: null;
    $opponentDwz = array_values(array_filter(array_map('intval', $opponentDwz), fn($rating) => $rating > 0));
    $n = count($opponentDwz);
    if ($points < 0 || $points > $n || fmod($points * 2, 1) != 0) {
      throw new \InvalidArgumentException("Invalid score $points for $n games");
    }
    if ($index < 1) {
      throw new \InvalidArgumentException("Invalid index $index");
    }
    if (!$n) {
      return new DwzCalculation(0, $points, $dwz, null, unavailableReason: DwzUnavailableReason::NoRatedGames);
    }
    $average = (int) round(array_sum($opponentDwz) / $n);

    if (!$dwz) {
      // First rating (3.4).
      $reason = match (true) {
        $n < self::MIN_GAMES_FIRST_RATING => DwzUnavailableReason::TooFewGamesForFirstRating,
        $points < 0.5 => DwzUnavailableReason::NoPointsForFirstRating,
        default => null,
      };
      if ($reason) {
        return new DwzCalculation($n, $points, null, $average, unavailableReason: $reason);
      }
      $newDwz = self::correctFirstRating((int) round(self::iterate($opponentDwz, $points)));
      return new DwzCalculation($n, $points, null, $average, newDwz: $newDwz);
    }

    $age = $yearOfBirth ? ($evaluationYear ?? (int) date('Y')) - $yearOfBirth : null;
    $expected = self::expectedScore($dwz, $opponentDwz);
    $k = self::developmentFactor($dwz, $index, $age, $points, $expected);
    return new DwzCalculation(
      games: $n,
      points: $points,
      oldDwz: $dwz,
      opponentAverage: $average,
      expectedScore: $expected,
      developmentFactor: $k,
      performance: self::performance($opponentDwz, $points),
      // Rn = R0 + K * (W - We), rounded to an integer, at least 1100 (3.5.2).
      newDwz: max(self::MIN_DWZ, (int) round($dwz + $k * ($points - $expected))),
    );
  }

  /**
   * The scoring probability P(D) for a rating difference D (3.3.1): a normal distribution with
   * standard deviation 200 * sqrt(2), i.e. P(D) = (1 + erf(D / 400)) / 2.
   */
  static function probability(float $difference): float {
    return (1 + self::erf($difference / 400)) / 2;
  }

  /**
   * The expected score We, i.e. the sum of the scoring probabilities (3.3.2).
   */
  static function expectedScore(float $dwz, array $opponentDwz): float {
    return array_sum(array_map(fn($opponent) => self::probability($dwz - $opponent), $opponentDwz));
  }

  /**
   * The development factor K = K0 * b + a, rounded to one decimal and limited to Kmax (Anhang 1.3).
   *
   * @param int|null $age age in the year of the evaluation, or null if unknown (treated as adult)
   */
  static function developmentFactor(int $dwz, int $index, int|null $age, float $points, float $expected): float {
    $junior = $age !== null && $age <= 25;
    $table = match (true) {
      $junior && $dwz < 2200 => self::K0_JUNIOR,
      !$junior && $dwz < 2000 => self::K0_ADULT,
      default => self::K0_STRONG,
    };
    $k0 = $table[min($index, 11) - 1];

    // Success bonus a.
    $a = 0;
    if ($points >= $expected) {
      if ($age !== null && $age <= 20) {
        if ($dwz < 2000) $a = (2000 - $dwz) / self::T;
      } else if ($dwz < 1600) {
        $a = $junior ? self::A1 : self::A2;
      }
    }

    // Brake factor b.
    $b = $points < $expected && $dwz < 1600 ? ($dwz + self::U) / (1600 + self::U) : 1;

    return min(self::K_MAX, round($k0 * $b + $a, 1));
  }

  /**
   * The performance Rh of an established player (3.6), or null with fewer than 5 games or 0%.
   */
  static function performance(array $opponentDwz, float $points): int|null {
    if (count($opponentDwz) < self::MIN_GAMES_PERFORMANCE || $points == 0) {
      return null;
    }
    return (int) round(self::iterate($opponentDwz, $points));
  }

  /**
   * Correction of the iteration result Ri to the first rating R1 (3.4.4.2, Anhang 1.2.2).
   */
  static function correctFirstRating(int $ri): int {
    $ri = max(0, $ri);
    return (int) round(match (true) {
      $ri >= 2000 => $ri,
      $ri >= self::MIN_DWZ => $ri + self::R * (2000 - $ri),
      default => self::MIN_DWZ + 9 * self::R * $ri / 11,
    });
  }

  /**
   * Finds the rating at which the expected score equals the actual score (3.4.4, 3.6.2). For a
   * 100% score, a fictitious draw against the opponents' average is added (3.4.1.3, 3.6.2).
   *
   * Instead of the iteration described in Anhang 1.1, this solves the equation numerically
   * with floating point arithmetic, which the Wertungsordnung explicitly allows (Anhang 2.1).
   */
  private static function iterate(array $opponentDwz, float $points): float {
    if ($points == count($opponentDwz)) {
      $opponentDwz[] = (int) round(array_sum($opponentDwz) / count($opponentDwz));
      $points += 0.5;
    }
    // The expected score is strictly increasing in the rating, so bisect.
    $low = min($opponentDwz) - 2000;
    $high = max($opponentDwz) + 2000;
    while ($high - $low > 0.001) {
      $mid = ($low + $high) / 2;
      if (self::expectedScore($mid, $opponentDwz) < $points) {
        $low = $mid;
      } else {
        $high = $mid;
      }
    }
    return ($low + $high) / 2;
  }

  /**
   * The error function, which PHP lacks. Uses the Chebyshev approximation of erfc from
   * Numerical Recipes (fractional error below 1.2e-7 everywhere).
   */
  private static function erf(float $x): float {
    $z = abs($x);
    $t = 1 / (1 + 0.5 * $z);
    $erfc = $t * exp(-$z * $z - 1.26551223 + $t * (1.00002368 + $t * (0.37409196 + $t * (0.09678418
        + $t * (-0.18628806 + $t * (0.27886807 + $t * (-1.13520398 + $t * (1.48851587
        + $t * (-0.82215223 + $t * 0.17087277)))))))));
    return $x >= 0 ? 1 - $erfc : $erfc - 1;
  }
}
