<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Core\Regulation;
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
  public string|null $result;
  public string|null $opponentResult;
  public Team $opponentTeam;
  public Player|null $opponentPlayer;
  public string $uri;

  public static function forPlayer(int $playerId, Entity\Game $game) {
    $home = $game->player1 && $game->player1->id === $playerId;
    $guest = $game->player2 && $game->player2->id === $playerId;
    if (!$home && !$guest) {
      throw new \Exception("Player did not participate in this game");
    }

    $result = new PlayerGame();
    $result->round = $game->pairing->round;
    $result->board = $game->board;
    $result->home = $home;
    $result->white = Regulation::isWhiteGame($home, $game->board, $game->pairing->division->league);
    $result->result = $home ? $game->result1 : $game->result2;
    $result->opponentResult = !$home ? $game->result1 : $game->result2;
    $result->opponentTeam = Team::fromEntity($home ? $game->pairing->team2 : $game->pairing->team1);
    $opponentPlayer = $home ? $game->player2 : $game->player1;
    $result->opponentPlayer = $opponentPlayer ? Player::fromEntity($opponentPlayer) : null;
    $result->uri = $game->pairing->division->round($result->round)->uri();
    return $result;
  }
}
