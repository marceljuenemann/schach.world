<?php

namespace Nsv\League\Api\Service\TieBreak;

use Nsv\League\Core\Regulation;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Team;

const MP_WIN = 2;
const MP_DRAW = 1;
const MP_LOSS = 0;

/**
 * Primary TieBreak based on Match Points and Board Points.
 * 
 * The scores are calculated eagerly in the constructor.
 */
class Scores implements TieBreak {
  /**
   * There are two supported ways to calculate match points, which only make a
   * difference when both teams left a board empty (a -:- score):
   * 1. The team with more board points wins the match.
   * 2. Scoring half of the possible board points is a draw, more is a win.
   * 
   * The following field is set to null in case (1), and to the number of
   * board points required for a draw in case (2).
   */
  private int | null $bpForDraw;

  private array $mps = [];  // Team ID => Match Points
  private array $bps = [];  // Team ID => Board Points

  public function __construct(
    public readonly Division $division,
    public readonly array $pairings
  ) {
    if (Regulation::hasMatchPointsMinimum($division->league)) {
      $this->bpForDraw = ceil($division->boardCount() / 2);
    } else {
      $this->bpForDraw = null;
    }
    [$this->mps, $this->bps] = $this->calculateScores($pairings);
  }
 
  /**
   * Calculates scores for all teams for the given pairings.
   */
  public function calculateScores(array $pairings): array {
    $mps = [];
    $bps = [];
    foreach ($pairings as $pairing) {
      [$mp1, $mp2] = $this->scoreMp($pairing);
      $mps[$pairing->team1->id] = $mp1 + ($mps[$pairing->team1->id] ?? 0);
      $mps[$pairing->team2->id] = $mp2 + ($mps[$pairing->team2->id] ?? 0);
      $bps[$pairing->team1->id] = $pairing->result1 + ($bps[$pairing->team1->id] ?? 0.0);
      $bps[$pairing->team2->id] = $pairing->result2 + ($bps[$pairing->team2->id] ?? 0.0);
    }
    return [$mps, $bps];
  }

  /**
   * Calculates match points for both teams in a pairing.
   */
  private function scoreMp(Pairing $pairing): array {
    // Not played yet or voided as 0:0.
    if (!$pairing->result1 && !$pairing->result2) {
      return [0, 0];
    }
    // If a bpForDraw is set, use that to calculate match points.
    if ($this->bpForDraw !== null) {
      return [
        $pairing->result1 > $this->bpForDraw ? MP_WIN : ($pairing->result1 == $this->bpForDraw ? MP_DRAW : MP_LOSS),
        $pairing->result2 > $this->bpForDraw ? MP_WIN : ($pairing->result2 == $this->bpForDraw ? MP_DRAW : MP_LOSS),
      ];
    }
    // Otherwise just compare board points.
    $mp1 = $pairing->result1 > $pairing->result2 ? MP_WIN : ($pairing->result1 < $pairing->result2 ? MP_LOSS : MP_DRAW);
    $mp2 = MP_WIN - $mp1;
    return [$mp1, $mp2];
  }

  public function matchPoints($teamId): int {
    return $this->mps[$teamId] ?? 0;
  }

  public function boardPoints($teamId): float {
    return $this->bps[$teamId] ?? 0.0;
  }

  public function compare(Team $a, Team $b): int {
    return $this->matchPoints($b->id) <=> $this->matchPoints($a->id) ?:
           $this->boardPoints($b->id) <=> $this->boardPoints($a->id);
  }
}
