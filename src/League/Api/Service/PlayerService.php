<?php

namespace Nsv\League\Api\Service;

use Nsv\Dwz\IsewaseDwzCalculator;
use Nsv\League\Api\Model\Player;
use Nsv\League\Core\Result;
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

  // TODO: cache, especially for the DWZ calculation.
  public function player(Entity\League $league, int $playerId): Player {
    $player = $this->playerRepository->find($playerId);
    if (!$player || $player->team->league != $league) {
      throw new NotFoundHttpException("Player not found");
    }

    $result = Player::fromEntity($player);
    foreach ($this->gameRepository->findByPlayer($player) as $game) {
      $result->addGame($game);
    }
    $result->dwzCalculation = $this->dwzCalc($result, $player->yearOfBirth());
    return $result;
  }

  private function dwzCalc(Player $player, int|null $yearOfBirth): array|null {
    $calculator = new IsewaseDwzCalculator();
    $opponentDwz = array();
    $points = 0.0;
    foreach ($player->games as $game) {
      // Only count actually played games against opponents with DWZ.
      if (Result::wasPlayed($game->result) && $game->opponentPlayer->dwz) {
        $opponentDwz[] = $game->opponentPlayer->dwz;
        $points += Result::score($game->result);
      }
    }
    return $calculator->calculate($player->dwz, $opponentDwz, $points, $yearOfBirth);
  }
}
