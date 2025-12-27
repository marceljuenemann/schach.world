<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Entity\Division;

/**
 * Represents a ranking.
 */
class Ranking
{
  // TeamRanking objects.
  public array $teams = [];

  public int $teamsPromoted;
  public int $teamsDemoted;
  public int $teamsMaybePromoted;
  public int $teamsMaybeDemoted;

  public function toLegacyFormat(Division $division): array {
    if (empty($this->teams)) {
      return [];
    }

    $header = array_merge(
      ['', 'Mannschaft'],
      range(1, count($this->teams)),
      ['MP', 'BP']
    );
    $result = array_merge(
      [$header],
      array_map(fn($team) => $team->toLegacyFormat($division->id), $this->teams)
    );
    for ($i = 1; $i < count($result); $i++) {
      $result[$i][$i + 1] = 'xxx';
    }

    for ($i = 1; $i <= $this->teamsPromoted && $i <= count($this->teams); $i++) {
      $result[$i][count($result[$i]) - 1] = 'aufsteiger';
    }

    for ($i = 1; $i <= $this->teamsDemoted && $i <= count($this->teams); $i++) {
      $row = count($result) - $i;
      $result[$row][count($result[$row]) - 1] = 'absteiger';
    }

    return $result;
  }
}
