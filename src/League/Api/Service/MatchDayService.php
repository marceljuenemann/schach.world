<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\Division;
use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Api\Model\Pairing;
use Nsv\League\Entity;
use Nsv\League\Repository\PairingRepository;

/**
 * Provides data and operations for the match day view.
 */
class MatchDayService
{
  function __construct(private PairingRepository $pairingRepository) {}

  public function matchDay(Entity\Division $division, int $round, callable $legacyRanking) {
    $pairings = $this->pairingRepository->findByRound($division, $round);

    $model = MatchDay::create($division, $round, $division->dateOfRound($round));
    foreach ($pairings as $pairing) {
      $model->pairings[] = Pairing::fromEntityWithGames($pairing);
    }
    $model->ranking = $legacyRanking();

    // TODO: Process last modified
    $comment = $division->roundComment($round);
    $model->comment = $comment ? $comment->text : null;

    return $model;
  }
}
