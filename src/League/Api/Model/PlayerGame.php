<?php

namespace Nsv\League\Api\Model;

use Nsv\Dwz\DsbDatabase;
use Nsv\League\Entity;

/**
 * A game from the perspective of a specific player.
 */
class PlayerGame
{
  public int $round;
  public int $board;
  public bool $home;
  public bool $white;
  public string $result;
  public string $opponentResult;
  public Team $opponentTeam;
  public Player $opponentPlayer;

  public static function forPlayer(Player $player, Entity\Game $game) {
    if ($game->player1->id != $player->id && $game->player2->id != $player->id) {
      throw new \Exception("Player did not participate in this game");
    }

    $result = new PlayerGame();
    $result->round = $game->pairing->round;
    $result->board = $game->board;
    $result->home = $game->player1->id == $player->id;
    $result->result = $result->home ? $game->result1 : $game->result2;
    $result->opponentResult = !$result->home ? $game->result1 : $game->result2;
    $result->opponentTeam = Team::fromEntity($result->home ? $game->pairing->team2 : $game->pairing->team1);
    $result->opponentPlayer = Player::fromEntity($result->home ? $game->player2 : $game->player1);
    // TODO: color:
    return $result;
  }
}
