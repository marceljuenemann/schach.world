<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\Player;
use Nsv\League\Api\Model\PlayerGame;
use Nsv\League\Api\Model\Team;
use Nsv\League\Api\Model\TeamPairing;
use Nsv\League\Core\Result;
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

    // Fetch basic info and players.
    $model = Team::fromEntity($team, true);
    $model->playersByTeamNumber = [];
    $players = [];
    foreach ([$team, ...$team->substituteTeams()] as $t) {
      foreach ($t->players as $player) {
        $players[$player->id] = Player::fromEntity($player);
        $players[$player->id]->gameCount = 0;
        $players[$player->id]->points = 0.0;
        $model->playersByTeamNumber[$t->number][] = $players[$player->id];
      }
    }
    ksort($model->playersByTeamNumber);

    // Fetch pairings and games.
    $pairings = $this->pairingRepository->findByTeam($teamId);
    foreach ($pairings as $pairing) {
      $tp = TeamPairing::forTeam($team, $pairing);
      $model->pairingsByDivision[$pairing->division->id][] = $tp;
      foreach ($pairing->games as $game) {
        $player = $tp->home ? $game->player1 : $game->player2;
        if ($player && isset($players[$player->id])) {
          // Store game on the player and keep stats.
          $pg = PlayerGame::forPlayer($player->id, $game);
          $players[$player->id]->games[$pairing->id] = $pg;
          if (Result::wasPlayed($pg->result)) {
            $players[$player->id]->gameCount += 1;            
            $players[$player->id]->points += Result::score($pg->result);
          }
        }
      }
    }
    
    return $model;
  }

}
