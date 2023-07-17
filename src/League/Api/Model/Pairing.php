<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Core\Result;
use Nsv\League\Entity;

class Pairing
{
  public Team $team1;
  public Team $team2;
  public ?Team $host;
  public ?string $result;
  public ?float $result1;
  public ?float $result2;
  public bool $wasMoved;
  public ?string $moveDate;

  public static function fromEntity(Entity\Pairing $pairing) {
    $result = new Pairing();
    $result->team1 = Team::fromEntity($pairing->team1);
    $result->team2 = Team::fromEntity($pairing->team2);
    $result->host = $pairing->host ? Team::fromEntity($pairing->host) : null;
    $result->result1 = $pairing->result1;
    $result->result2 = $pairing->result2;
    $result->result = self::formatResult($pairing);
    $result->wasMoved = $pairing->wasMoved();
    $result->moveDate = $pairing->moveDate();
    return $result;
  }

  private static function formatResult(Entity\Pairing $pairing): string|null {
    if ($pairing->result1 === null) return null;
    return Result::format($pairing->result1).' : '.Result::format($pairing->result2);
  }
}
