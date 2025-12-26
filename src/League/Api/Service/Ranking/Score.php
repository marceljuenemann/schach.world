<?php

namespace Nsv\League\Chess\Ranking;

use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Team;

/**
 * Primary TieBreak based on Match Points and Board Points.
 * 
 * The scores are calculated eagerly in the constructor.
 */
class Score extends TieBreak {
  // Board points required for a draw. If null, we just compare board points.
  private int | null $bpForDraw;

  private array $mps = [];  // Team => Match Points
  private array $bps = [];  // Team => Board Points

  public function __construct(public readonly Ranking $ranking) {
    parent::__construct($ranking);
    // TODO: Configure based on organisation rules.
    $this->bpForDraw = $ranking->division->boardCount() / 2;
    [$this->mps, $this->bps] = $this->calculateScores(
      $ranking->teams(),
      $ranking->division->pairingsForRound($ranking->round)
    );
  }
 
  /**
   * Calculates scores for all teams for the given pairings.
   */
  public function calculateScores(array $teams, array $pairings): array {
    $mps = [];
    $bps = [];
    foreach ($teams as $team) {
      $mps[$team] = 0;
      $bps[$team] = 0.0;
    }
    foreach ($pairings as $pairing) {
      [$mp1, $mp2] = $this->scoreMp($pairing);
      $mps[$pairing->team1] += $mp1;
      $mps[$pairing->team2] += $mp2;
      $bps[$pairing->team1] += $pairing->result1;
      $bps[$pairing->team2] += $pairing->result2;
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
        $pairing->result1 > $this->bpForDraw ? 2 : ($pairing->result1 == $this->bpForDraw ? 1 : 0),
        $pairing->result2 > $this->bpForDraw ? 2 : ($pairing->result2 == $this->bpForDraw ? 1 : 0),
      ];
    }
    // Otherwise just compare board points.
    $mp1 = $pairing->result1 > $pairing->result2 ? 2 : ($pairing->result1 < $pairing->result2 ? 0 : 1);
    $mp2 = 2 - $mp1;
    return [$mp1, $mp2];
  }

  public function matchPoints(Team $team): int {
    return $this->mps[$team->id] ?? 0;
  }

  public function boardPoints(Team $team): float {
    return $this->bps[$team->id] ?? 0.0;
  }

  public function compare(Team $a, Team $b): int {
    $diff = $this->matchPoints($b) <=> $this->matchPoints($a);
    if ($diff !== 0) {
      return $diff;
    }
    return $this->boardPoints($b) <=> $this->boardPoints($a);
  }
}
