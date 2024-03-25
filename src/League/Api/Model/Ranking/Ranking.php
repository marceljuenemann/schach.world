<?php

namespace Nsv\League\Api\Model\Ranking;

use Nsv\League\Entity;
use Nsv\League\Entity\Division;

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
      return new RankingTeam($team);
    }, $this->division->teams())];
    
  }


}
