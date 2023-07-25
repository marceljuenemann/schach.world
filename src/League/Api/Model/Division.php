<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Entity;

class Division
{
  public int $id;
  public string $name;
  public string $matchDayUri;
  public string $statsUri;
  public ?array $matchDays;
  
  public static function fromEntity(Entity\Division $division) {
    $result = new Division();
    $result->id = $division->id;
    $result->name = $division->name;
    $result->matchDayUri = $division->matchDayUri();
    $result->statsUri = $division->statsUri();
    return $result;
  }
}
