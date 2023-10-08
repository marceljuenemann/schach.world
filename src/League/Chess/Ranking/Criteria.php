<?php

namespace Nsv\League\Chess\Ranking;

/**
 * Abstract criteria for sorting the ranking.
 */
abstract class Criteria {

  /**
   * Sorts the teams into an array of arrays, such that teams with the same ranking
   * are in the same position (e.g. [[pos 1], [pos 2, pos 2], [pos 4]]).
   */
  abstract public function sort(array $teams, array $pairings): array;
}
