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

  public function uri(): string {
    return $this->division->uri() . $this->round;
  }

  public function pdfUri(): string {
    return $this->division->league->uri() . "?staffel={$this->division->id}&r={$this->round}&ausgabe=pdf";
  }

  public function apiUri(): string {
    return $this->division->league->uri() . "api/divisions/{$this->division->path()}/rounds/{$this->round}/";
  }
}
