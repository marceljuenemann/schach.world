<?php

namespace League\Api\Model;

use Nsv\League\Entity\Team;

/**
 * Represents a team in the ranking table.
 * Contains all necessary data like points, ranking-position etc.
 */
class RankingTeam {
  public Team $team;
  public array $pairings;
  public array $crosstable_pairings;
  public int $ranking_position = 0;
  public ?int $team_points;
  public ?float $board_points;
}