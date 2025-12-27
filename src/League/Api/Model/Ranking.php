<?php

namespace Nsv\League\Api\Model;

/**
 * Represents a ranking.
 */
class Ranking
{
  // TeamRanking objects.
  public array $teams = [];

  // TODO: Aufsteiger etc.

  public function toLegacyFormat(): array {
    $result = array_map(fn($team) => $team->toLegacyFormat(), $this->teams);
    return $result;
  }
}
