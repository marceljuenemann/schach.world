<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Entity;

class Division
{
  public int $id;
  public string $name;
  public string $matchDayUri;
  public string $scheduleUri;
  public string $statsUri;
  public ?array $matchDays;

  public function hasPairings(): bool {
    if (isset($this->matchDays)) {
      foreach ($this->matchDays as $matchDay) {
        if (count($matchDay->pairings)) {
          return true;
        }
      }
    }
    return false;
  }
  
  public static function fromEntity(Entity\Division $division) {
    $result = new Division();
    $result->id = $division->id;
    $result->name = $division->name;
    $result->matchDayUri = $division->matchDayUri();
    $result->scheduleUri = $division->scheduleUri();
    $result->statsUri = $division->statsUri();
    return $result;
  }
}
