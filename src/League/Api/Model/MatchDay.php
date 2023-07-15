<?php

namespace Nsv\League\Api\Model;

/**
 * A MatchDay is a collection of all pairings of a single round.
 */
class MatchDay
{
  public int $round;
  public ?string $date;
  public string $uri;
  public array $pairings = array();

  public static function compare(MatchDay $a, MatchDay $b) {
    if ($a->date == $b->date) return $a->round - $b->round;
    if ($a->date === null) return 1;
    if ($b->date === null) return -1;
    return $a->date < $b->date ? -1 : 1;
  }
}
