<?php

namespace Nsv\League\Chess\Ranking;

use Nsv\League\Entity;

/**
 * Represents a team in the ranking, containing pairings and points.
 */
class Team {



  public function __construct(private Entity\Team $entity) {}

}
