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

  function uri(): string {
    return $this->division->matchDayUri($this->round);
  }
}
