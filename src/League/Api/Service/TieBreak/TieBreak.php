<?php

namespace Nsv\League\Api\Service\TieBreak;

use Nsv\League\Entity\Team;

/**
 * A TieBreak for sorting the ranking.
 */
interface TieBreak {

  /**
   * Comparator for two teams in the ranking. May have side effects.
   */
  public function compare(Team $a, Team $b): int;
}
