<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Entity;

class Team
{
  public int $id;
  public string $name;
  public string $uri;
  public \stdClass $venue;
  public \stdClass $captain;

  public static function fromEntity(Entity\Team $team) {
    $result = new Team();
    $result->id = $team->id;
    $result->name = $team->nameWithNumber();
    $result->uri = $team->uri();
    $result->venue = self::venueFromEntity($team);
    $result->captain = self::captainFromEntity($team);
    return $result;
  }

  private static function venueFromEntity(Entity\Team $team): \stdClass {
    $result = new \stdClass();
    $result->name = $team->venueName;
    $result->note = $team->venueNote;
    $result->street = $team->venueStreet;
    $result->postCode = $team->venuePostCode;
    $result->city = $team->venueCity;
    $result->phone = $team->venuePhone;
    return $result;
  }

  private static function captainFromEntity(Entity\Team $team): \stdClass {
    $result = new \stdClass();
    $result->name = $team->captainName;
    $result->mail = $team->captainMail;
    $result->phone = $team->captainPhone;
    $result->phone2 = $team->captainPhone2;
    return $result;
  }
}
