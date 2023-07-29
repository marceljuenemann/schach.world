<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\Team;
use Nsv\League\Api\Model\TeamPairing;
use Nsv\League\Entity;
use Nsv\League\Repository\PairingRepository;

class TeamService
{
  function __construct(
    private PairingRepository $pairingRepository
  ) {}

  // TODO: cache.
  public function team(Entity\League $league, int $teamId): Team {
    $team = $league->teamById($teamId);
    $model = Team::fromEntity($team, true);

    // Fetch pairings and games.

    $pairings = $this->pairingRepository->findByTeam($teamId);

    foreach ($pairings as $pairing) {
      $model->pairingsByDivision[$pairing->division->id][] = TeamPairing::forTeam($team, $pairing);
    }


    return $model;
  }

}
