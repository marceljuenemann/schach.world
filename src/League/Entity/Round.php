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

  /**
   * Returns all pairings of this round. Uses Division->pairings, which is always
   * loaded when on a division specific page.
   */
  public function pairings(): array {
    $pairings = [];
    foreach ($this->division->pairings as $pairing) {
      if ($pairing->round == $this->round) {
        $pairings[] = $pairing;
      }
    }
    return $pairings;
  }

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
