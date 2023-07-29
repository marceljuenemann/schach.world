<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Core\Result;
use Nsv\League\Entity;

/**
 * A pairing from the perspective of a specific team.
 */
class TeamPairing
{
  public int $id;
  public Team $opponent;
  public int $round;
  public bool $home;
  public string|null $date;
  public ?string $result;
  public string $uri;

  public static function forTeam(Entity\Team $team, Entity\Pairing $pairing) {
    $result = new TeamPairing();
    $result->id = $pairing->id;
    $result->round = $pairing->round;
    $result->date = $pairing->wasMoved() ? $pairing->moveDate() : $pairing->division->dateOfRound($pairing->round);
    $result->uri = $pairing->division->matchDayUri($pairing->round);

    if ($pairing->team1 == $team) {
      $result->home = true;
      $result->opponent = Team::fromEntity($pairing->team2);
      if ($pairing->result1 !== null) {
        $result->result = Result::format($pairing->result1).':'.Result::format($pairing->result2);
      }
    } else {
      $result->home = false;
      $result->opponent = Team::fromEntity($pairing->team1);
      if ($pairing->result1 !== null) {
        $result->result = Result::format($pairing->result2).':'.Result::format($pairing->result1);
      }
    }

    return $result;
  }
}
