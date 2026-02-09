<?php

namespace Nsv\League\Api\Service\TieBreak;

use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Team;

/**
 * Implements the "Head to Head" tie break ("Direkter Vergleich" in German).
 * 
 * This works very similiar to the Scores tie break, but only considers pairings
 * teams that have the same overall scores.
 * 
 * The scores are calculated eagerly in the constructor.
 */
class HeadToHead implements TieBreak {
  private array $mps = [];  // Team ID => Match Points
  private array $bps = [];  // Team ID => Board Points

  public function __construct(private readonly Scores $scores) {
    $pairings = array_filter($scores->pairings, function (Pairing $pairing) {
      return $this->scores->matchPoints($pairing->team1->id) ===
             $this->scores->matchPoints($pairing->team2->id) &&
             $this->scores->boardPoints($pairing->team1->id) ===
             $this->scores->boardPoints($pairing->team2->id);
    });
    [$this->mps, $this->bps] = $this->scores->calculateScores($pairings);
  }
 
  private function matchPoints($teamId): int {
    return $this->mps[$teamId] ?? 0;
  }

  private function boardPoints($teamId): float {
    return $this->bps[$teamId] ?? 0.0;
  }

  public function compare(Team $a, Team $b): int {
    return $this->matchPoints($b->id) <=> $this->matchPoints($a->id) ?:
           $this->boardPoints($b->id) <=> $this->boardPoints($a->id);
  }
}
