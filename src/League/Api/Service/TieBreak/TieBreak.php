<?php

namespace Nsv\League\Api\Service\TieBreak;

use Nsv\League\Entity\Team;

/**
 * A TieBreak for sorting the ranking.
 */
abstract class TieBreak {

  /**
   * Comparator for two teams in the ranking. May have side effects.
   */
  abstract public function compare(Team $a, Team $b): int;
}
