<?php

namespace Nsv\League\Api\Model\Ranking;

use Nsv\League\Entity\Team;

/**
 * Represents a team in the ranking, containing pairings and points.
 */
class RankingTeam {

  public string $name;
  public string $uri;
  public ?array $matches;
  public ?int $teamPoints;
  public ?int $boardPoints;



  public function __construct(private Team $entity) {}

}
