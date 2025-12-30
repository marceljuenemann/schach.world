<?php

namespace Nsv\League\Api\Service\TieBreak;

use Nsv\League\Entity\Team;

/**
 * Comparator that compares based on multiple provided comparators.
 */
class TieBreaks implements TieBreak {

  public function __construct(
    private array $tiebreaks
  ) {}

  public function compare(Team $a, Team $b): int {
    foreach ($this->tiebreaks as $tiebreak) {
      $cmp = $tiebreak->compare($a, $b);
      if ($cmp !== 0) {
        return $cmp;
      }
    }
    return 0;
  }

  /**
   * Sorts the given array according to the tie breaks.
   */
  public function sort(array &$values) {
    usort($values, [$this, 'compare']);
  }
}
