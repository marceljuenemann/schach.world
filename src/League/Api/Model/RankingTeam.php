<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Entity\Team;

/**
 * Represents a team in the ranking table.
 * Contains all necessary data like points, ranking-position etc.
 */
class RankingTeam {
  public Team $team;
  public string $name;
  public array $pairings;
  public array $crosstable_pairings;
  public int $ranking_position = 0;
  public int $team_points = 0;
  public float $board_points = 0;
  public bool $tied_after_berlin = false;
  public string $relegation = 'none';
}