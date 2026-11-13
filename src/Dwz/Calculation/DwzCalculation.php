<?php

namespace Nsv\Dwz\Calculation;

/**
 * Result of an inofficial DWZ calculation for a single player, see DwzCalculator.
 *
 * If no new DWZ could be calculated, $newDwz is null and $unavailableReason explains why.
 */
final readonly class DwzCalculation
{
  public function __construct(
    /** Number of games against rated opponents (n). */
    public int $games,
    /** Score in the games against rated opponents (W). */
    public float $points,
    /** DWZ before the calculation (R0), null for unrated players. */
    public int|null $oldDwz,
    /** Average DWZ of the opponents (Rc), null without games. */
    public int|null $opponentAverage,
    /** Expected score (We), only for rated players. */
    public float|null $expectedScore = null,
    /** Development factor K, only for rated players. */
    public float|null $developmentFactor = null,
    /** Performance (Rh), only for rated players with at least 5 games and more than 0 points. */
    public int|null $performance = null,
    /** New DWZ (Rn or first rating R1). */
    public int|null $newDwz = null,
    public DwzUnavailableReason|null $unavailableReason = null,
  ) {}

  /**
   * Change of the DWZ, only for rated players.
   */
  public function difference(): int|null {
    return $this->oldDwz && $this->newDwz !== null ? $this->newDwz - $this->oldDwz : null;
  }
}
