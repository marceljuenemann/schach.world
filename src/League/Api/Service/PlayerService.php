<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\Game;
use Nsv\League\Api\Model\Player;
use Nsv\League\Entity;
use Nsv\League\Repository\GameRepository;
use Nsv\League\Repository\PlayerRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlayerService
{
  function __construct(
    private PlayerRepository $playerRepository,
    private GameRepository $gameRepository
  ) {}

  public function player(Entity\League $league, int $playerId): Player {
    $player = $this->playerRepository->find($playerId);
    if (!$player || $player->team->league != $league) {
      throw new NotFoundHttpException("Player not found");
    }

    $result = Player::fromEntity($player);
    foreach ($this->gameRepository->findByPlayer($player) as $game) {
      $result->addGame($game);
    }
    return $result;
  }
}
