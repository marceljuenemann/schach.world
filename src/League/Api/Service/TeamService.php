<?php

namespace Nsv\League\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Api\Model\Player;
use Nsv\League\Api\Model\PlayerGame;
use Nsv\League\Api\Model\Team;
use Nsv\League\Api\Model\TeamPairing;
use Nsv\League\Api\Request\UpdateTeamVenueRequest;
use Nsv\League\Core\Result;
use Nsv\League\Entity;
use Nsv\League\Repository\PairingRepository;

class TeamService
{
  function __construct(
    private PairingRepository $pairingRepository,
    private EntityManagerInterface $leagueEntityManager
  ) {}

  public function team(Entity\Team $team): Team {
    // Fetch basic info and players.
    $model = Team::fromEntityWithDetails($team);
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
    $pairings = $this->pairingRepository->findByTeam($team);
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

  public function updateVenue(Entity\Team $team, UpdateTeamVenueRequest $request) {
    $team->venueName = $request->name;
    $team->venueNote = $request->note;
    $team->venueStreet = $request->street;
    $team->venuePostCode = $request->postCode;
    $team->venueCity = $request->city;
    $team->venuePhone = $request->phone;
    // TODO: Allow updating accessibility.
    $this->leagueEntityManager->persist($team);
    $this->leagueEntityManager->flush();
  }
}
