<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\Team;
use Nsv\League\Entity;

class TeamService
{
  function __construct(

    ) {}

  // TODO: cache.
  public function team(Entity\League $league, int $teamId): Team {
    $team = $league->teamById($teamId);
    $model = Team::fromEntity($team);
    return $model;
  }

}
