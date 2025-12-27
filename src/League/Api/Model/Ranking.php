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

    $lastCol = count($result[1]) - 1;
    for ($i = 1; $i <= $this->teamsPromoted && $i <= count($this->teams); $i++) {
      $result[$i][$lastCol] = 'aufsteiger';
    }
    for ($i = 1; $i <= $this->teamsMaybePromoted && $i + $this->teamsPromoted <= count($this->teams); $i++) {
      $result[$i + $this->teamsPromoted][$lastCol] = 'aufsteigerRelegation';
    }
    for ($i = 1; $i <= $this->teamsDemoted && $i <= count($this->teams); $i++) {
      $result[count($result) - $i][$lastCol] = 'absteiger';
    }
    for ($i = 1; $i <= $this->teamsMaybeDemoted && $i + $this->teamsDemoted <= count($this->teams); $i++) {
      $result[count($result) - $i - $this->teamsDemoted][$lastCol] = 'absteigerRelegation';
    }

    return $result;
  }
}
