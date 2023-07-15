<?php

namespace Nsv\League\Entity;

/**
 * A MatchDay is a collection of all pairings of a single round.
 */
class MatchDay
{
  function __construct(
    public readonly Division $division,
    public readonly int $round,
    public readonly array $pairings,
    public readonly ?string $date
  ) {}

  // TODO: Outsource into a Twig function.
  public function formattedDate() {
    if (!$this->date) return '';
    return date('d.m.Y', strtotime($this->date)); 
  }

  public static function compare(MatchDay $a, MatchDay $b) {
    if ($a->date == $b->date) return $a->round - $b->round;
    if ($a->date === null) return 1;
    if ($b->date === null) return -1;
    return $a->date < $b->date ? -1 : 1;
  }
}
