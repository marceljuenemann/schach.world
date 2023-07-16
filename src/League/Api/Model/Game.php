<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Entity;

class Game
{
  public int $board;
  public Player $player1;
  public Player $player2;
  public string $result1;
  public string $result2;

  public static function fromEntity(Entity\Game $game) {
    $result = new Game();
    $result->board = $game->board;
    $result->player1 = Player::fromEntity($game->player1);
    $result->player2 = Player::fromEntity($game->player2);
    $result->result1 = $game->result1;
    $result->result2 = $game->result2;
    return $result;
  }
}
