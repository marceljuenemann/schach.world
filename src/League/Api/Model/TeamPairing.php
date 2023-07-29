<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Core\Result;
use Nsv\League\Entity;

/**
 * A pairing from the perspective of a specific team.
 */
class TeamPairing
{
  public Team $opponent;
  public Division $division;
  public int $round;
  public bool $home;
  public ?string $date;
  public ?string $result;

  public static function forTeam(Entity\Team $team, Entity\Pairing $pairing) {
    $result = new TeamPairing();
    $result->division = Division::fromEntity($pairing->division);
    $result->round = $pairing->round;
    // TODO: Date
    if ($pairing->team1 == $team) {
      $result->home = true;
      $result->opponent = Team::fromEntity($pairing->team2);
      if ($pairing->result1 !== null) {
        $result->result = Result::format($pairing->result1).' : '.Result::format($pairing->result2);
      }
    } else {
      $result->home = false;
      $result->opponent = Team::fromEntity($pairing->team1);
      if ($pairing->result1 !== null) {
        $result->result = Result::format($pairing->result2).' : '.Result::format($pairing->result1);
      }
    }
    return $result;
  }
}
