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
  public ?float $score;
  public ?float $opponentScore;
  public ?string $result;
  public string $uri;

  public static function forTeam(Entity\Team $team, Entity\Pairing $pairing) {
    $result = new TeamPairing();
    $result->id = $pairing->id;
    $result->round = $pairing->round;
    $result->date = $pairing->wasMoved() ? $pairing->moveDate() : $pairing->division->round($pairing->round)->date;
    // TODO: Add URL fragment to link directly to the pairing.
    $result->uri = $pairing->division->round($pairing->round)->uri();

    if ($pairing->team1 == $team) {
      $result->home = true;
      $result->opponent = Team::fromEntity($pairing->team2);
      if ($pairing->result1 !== null) {
        $result->score = $pairing->result1;
        $result->opponentScore = $pairing->result2;
      }
    } else {
      $result->home = false;
      $result->opponent = Team::fromEntity($pairing->team1);
      if ($pairing->result1 !== null) {
        $result->score = $pairing->result2;
        $result->opponentScore = $pairing->result1;
      }
    }
    if (isset($result->score)) {
      $result->result = Result::format($result->score).':'.Result::format($result->opponentScore);
    }
    return $result;
  }
}
