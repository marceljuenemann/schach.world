<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Api\Model\Pairing;
use Nsv\League\Api\Model\Player;
use Nsv\League\Entity;
use Nsv\League\Repository\PairingRepository;
use Nsv\League\Repository\PlayerRepository;

/**
 * Provides data and operations for the match day view.
 */
class MatchDayService
{
  function __construct(
    private PairingRepository $pairingRepository,
    private PlayerRepository $playerRepository
  ) {}

  public function matchDay(Entity\Division $division, int $round, callable $legacyRanking) {
    $model = MatchDay::create($division, $round, $division->round($round)->date);

    $pairings = $this->pairingRepository->findByRound($division, $round);
    foreach ($pairings as $pairing) {
      $model->pairings[] = Pairing::fromEntityWithGames($pairing);
      if (!isset($model->lastModified) || $pairing->lastModified > $model->lastModified) {
        $model->lastModified = $pairing->lastModified;
      }
    }

    $model->ranking = $legacyRanking();

    $comment = $division->round($round)->comment();
    if ($comment) {
      $model->comment = $comment->text;
      if (!isset($model->lastModified) || $comment->lastModified > $model->lastModified) {
        $model->lastModified = $comment->lastModified;
      }
    }

    if ($division->config('showLateRegistrations')) {
      foreach ($this->playerRepository->findLateRegistrations($division, $round, $round + 1) as $player) {
        $model->lateRegisteredPlayers[$player->team->id][] = Player::fromEntity($player);
      }
    }

    if ($division->config('showNextMatchDay')) {
      $nextRound = $division->round($round + 1);
      if ($nextRound) {
        $model->nextMatchDay = MatchDay::fromRound($nextRound);
        foreach ($this->pairingRepository->findByRounds([$nextRound]) as $pairing) {
          $model->nextMatchDay->pairings[] = Pairing::fromEntity($pairing);
        }
      }
    }

    return $model;
  }
}
