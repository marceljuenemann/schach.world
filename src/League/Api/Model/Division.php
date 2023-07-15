<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Entity;

class Division
{
  public int $id;
  public string $name;

  public static function fromEntity(Entity\Division $division) {
    $result = new Division();
    // TODO: Automate this with annotations? Or overkill?
    // TODO: needs to be unicode for API output to work...
    $result->id = $division->id;
    $result->name = $division->name;
    return $result;
  }
}
