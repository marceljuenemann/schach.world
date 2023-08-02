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
  public ?string $comment;
  public bool $wasMoved;
  public ?string $moveDate;

  public ?array $games;

  /**
   * Bye matches will only have games with bye results.
   */
  public function wasBye() {
    if ($this->result1 !== 0.0 && $this->result2 !== 0.0) return false;
    if (!$this->games) return false;
    foreach ($this->games as $game) {
      if (!Result::wasBye($game->result1) || !Result::wasBye($game->result2)) {
        return false;
      }
    }
    return true;
  }

  public static function fromEntity(Entity\Pairing $pairing) {
    $result = new Pairing();
    $result->team1 = Team::fromEntity($pairing->team1);
    $result->team2 = Team::fromEntity($pairing->team2);
    $result->host = $pairing->host ? Team::fromEntity($pairing->host) : null;
    $result->result1 = $pairing->result1;
    $result->result2 = $pairing->result2;
    $result->result = self::formatResult($pairing);
    $result->comment = $pairing->comment;
    $result->wasMoved = $pairing->wasMoved();
    $result->moveDate = $pairing->moveDate();
    return $result;
  }

  public static function fromEntityWithGames(Entity\Pairing $pairing) {
    $model = self::fromEntity($pairing);
    foreach ($pairing->games as $game) {
      $model->games[] = Game::fromEntity($game);
    }
    return $model;
  }

  private static function formatResult(Entity\Pairing $pairing): string|null {
    if ($pairing->result1 === null) return null;
    return Result::format($pairing->result1).' : '.Result::format($pairing->result2);
  }
}
 