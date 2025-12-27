<?php

namespace Nsv\League\Api\Service\TieBreak;

use Nsv\League\Core\Result;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Team;

/**
 * Implements the "Berliner Wertung" tie break.
 * 
 * For a definition, see: https://de.wikipedia.org/wiki/Feinwertung#Berliner_Wertung
 * 
 * The scores are calculated lazily only when needed, as each pairing requires a
 * database query to fetch the games of the pairing.
 */
class Berlin implements TieBreak {
  private array $cache = [];  // MP => BP => Team ID => Berlin Points

  public function __construct(private readonly Scores $scores) {}

  public function compare(Team $a, Team $b): int {
    assert($this->scores->compare($a, $b) === 0);
    $berlin = $this->scoreAll($this->scores->matchPoints($a->id), $this->scores->boardPoints($a->id));
    return ($berlin[$b->id] ?? 0.0) <=> ($berlin[$a->id] ?? 0.0);
  }

  /**
   * Calculates Berlin points for all teams with the given MP and BP.
   */
  private function scoreAll(int $mp, float $bp): array {
    if (isset($this->cache[$mp][$bp])) {
      return $this->cache[$mp][$bp];
    }
    $berlin = [];
    foreach ($this->scores->pairings as $pairing) {
      if ($this->scores->matchPoints($pairing->team1->id) === $mp &&
          $this->scores->boardPoints($pairing->team1->id) === $bp &&
          $this->scores->matchPoints($pairing->team2->id) === $mp &&
          $this->scores->boardPoints($pairing->team2->id) === $bp) {
        [$b1, $b2] = $this->scorePairing($pairing);
        $berlin[$pairing->team1->id] = ($berlin[$pairing->team1->id] ?? 0.0) + $b1;
        $berlin[$pairing->team2->id] = ($berlin[$pairing->team2->id] ?? 0.0) + $b2;
      }
    }
    $this->cache[$mp][$bp] = $berlin;
    return $berlin;
  }

  /**
   * Calculates Berlin points for a single pairing.
   */
  private function scorePairing(Pairing $pairing): array {
    $team1 = 0.0;
    $team2 = 0.0;
    $boardCount = $pairing->division->boardCount();
    foreach ($pairing->games as $game) {
      $multiplier = $boardCount - $game->board + 1;
      $team1 += Result::score($game->result1) * $multiplier;
      $team2 += Result::score($game->result2) * $multiplier;
    }
    return [$team1, $team2];
  }
}
