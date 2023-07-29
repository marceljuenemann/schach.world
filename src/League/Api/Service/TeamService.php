<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\Player;
use Nsv\League\Api\Model\Team;
use Nsv\League\Api\Model\TeamPairing;
use Nsv\League\Entity;
use Nsv\League\Repository\PairingRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TeamService
{
  function __construct(
    private PairingRepository $pairingRepository
  ) {}

  // TODO: cache.
  public function team(Entity\League $league, int $teamId): Team {
    $team = $league->teamById($teamId);
    if ($team->league != $league) {
      throw new NotFoundHttpException("Team not found");
    }

    $model = Team::fromEntity($team, true);

    // Fetch pairings and games.

    $pairings = $this->pairingRepository->findByTeam($teamId);

    foreach ($pairings as $pairing) {
      $model->pairingsByDivision[$pairing->division->id][] = TeamPairing::forTeam($team, $pairing);
    }

    // Fetch players.
    foreach ([$team, ...$team->substituteTeams()] as $t) {
      $model->playersByTeamNumber[$t->number] = array_map([Player::class, 'fromEntity'], $t->players->toArray());
    }
    ksort($model->playersByTeamNumber);
    
    return $model;
  }

}
