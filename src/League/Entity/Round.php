<?php

namespace Nsv\League\Entity;

/**
 * Helper object to idenfity a specific round of a division.
 */
class Round
{
  function __construct(
    public readonly Division $division,
    public readonly int $round,
    public readonly ?string $date
  ) {}

  public function comment(): RoundComment|null {
    // TODO: Create unique index on table.
    foreach ($this->division->roundComments as $comment) {
      if ($comment->round == $this->round) {
        return $comment;
      }
    }
    return null;
  }
  
  function uri(): string {
    return $this->division->matchDayUri($this->round);
  }
}
