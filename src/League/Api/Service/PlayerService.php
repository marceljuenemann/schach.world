<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\Player;
use Nsv\League\Entity;
use Nsv\League\Repository\PlayerRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlayerService
{
  function __construct(private PlayerRepository $playerRepository) {}

  public function player(Entity\League $league, int $playerId): Player {
    $player = $this->playerRepository->find($playerId);
    if (!$player || $player->team->league != $league) {
      throw new NotFoundHttpException("Player not found");
    }
    return Player::fromEntity($player);
  }
}
