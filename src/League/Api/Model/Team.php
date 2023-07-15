<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Entity;

class Team
{
  public int $id;
  public string $name;
  public string $uri;

  public static function fromEntity(Entity\Team $team) {
    $result = new Team();
    $result->id = $team->id;
    $result->name = $team->nameWithNumber();
    $result->uri = $team->league->linkUri() . "?mannschaft=" . $team->id;
    return $result;
  }
}
