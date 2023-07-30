<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Core\Encoding;
use Nsv\League\Entity;

class Team
{
  public int $id;
  public string $name;
  public ?string $zps;
  public string $uri;
  public \stdClass $venue;
  public \stdClass $captain;

  public ?array $pairingsByDivision;
  public ?array $playersByTeamNumber;

  public static function fromEntity(Entity\Team $team, bool $details = false) {
    $result = new Team();
    $result->id = $team->id;
    $result->name = $team->nameWithNumber();
    $result->zps = $team->zps;
    $result->uri = $team->uri();
    if ($details) {
      $result->venue = self::venueFromEntity($team);
      $result->captain = self::captainFromEntity($team);
    }
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
    $result->isAccessible = $team->isVenueAccessible();
    $result->hasAccessibleToilet = $team->hasAccessibleToilet();
    $result->directionsUri = self::mapsLink($result);
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

  private static function mapsLink($venue): string {
    $uri = "https://maps.google.com/maps?hl=de&amp;q=";
    $uri .= urlencode(Encoding::utf8_encode($venue->street));
    $uri .= ',' . urlencode($venue->postCode) . '+';
    $uri .= urlencode(Encoding::utf8_encode($venue->city));
    $uri .= ',Germany';
    return $uri;
  }
}
