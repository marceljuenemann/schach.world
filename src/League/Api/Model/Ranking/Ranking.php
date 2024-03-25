<?php

namespace Nsv\League\Chess\Ranking;

use Nsv\League\Entity\Division;
use Nsv\League\Entity;

/**
 * Ranking for a specific division and round.
 */
class Ranking {

  function __construct(private Division $division, private int $round) {}

  /**
   * TODO:
   * - Team classes
   * - Populate team with pairings
   * - MP+BP
   * - dV 
   * - Los?
   * - Siege
   */

   public function calculate() {
    $ranking = [array_map(function (Entity\Team $team) {
      return new Team($team);
    }, $this->division->teams())];
    
  }


}
