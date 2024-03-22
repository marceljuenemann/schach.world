<?php

namespace Nsv\League\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Api\Model\Player;
use Nsv\League\Api\Model\PlayerGame;
use Nsv\League\Api\Model\Team;
use Nsv\League\Api\Model\TeamPairing;
use Nsv\League\Api\Request\UpdateTeamCaptainRequest;
use Nsv\League\Api\Request\UpdateTeamRecipientsRequest;
use Nsv\League\Api\Request\UpdateTeamVenueRequest;
use Nsv\League\Core\Result;
use Nsv\League\Entity;
use Nsv\League\Entity\TeamRecipient;
use Nsv\League\Repository\PairingRepository;

class TeamService
{
  function __construct(
    private PairingRepository $pairingRepository,
    private EntityManagerInterface $leagueEntityManager
  ) {}

  public function team(Entity\Team $team, bool $additionalRecipients = false): Team {
    // Fetch basic info.
    $model = Team::fromEntityWithDetails($team);
    if ($additionalRecipients) {
      $model->additionalRecipients = array_map(function (TeamRecipient $recipient) {
        return $recipient->mail;
      }, \iterator_to_array($team->additionalRecipients));
    }

    // Fetch players.
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

  public function updateCaptain(Entity\Team $team, UpdateTeamCaptainRequest $request) {
    $team->captainName = $request->name;
    $team->captainMail = $request->mail;
    $team->captainPhone = $request->phone;
    $team->captainPhone2= $request->phone2;
    $this->leagueEntityManager->persist($team);
    $this->leagueEntityManager->flush();
  }

  public function updateRecipients(Entity\Team $team, UpdateTeamRecipientsRequest $request) {
    // Delete recipients no longer present.
    $existingRecipients = [];
    foreach ($team->additionalRecipients as $recipient) {
      if (array_search($recipient->mail, $request->recipients) === false) {
        $this->leagueEntityManager->remove($recipient);
      } else {
        $existingRecipients[$recipient->mail] = true;
      }
    }

    // Add recipients that didn't exist before.
    foreach ($request->recipients as $recipient) {
      if (!isset($existingRecipients[$recipient])) {
        $entity = new TeamRecipient();
        $entity->team = $team;
        $entity->mail = $recipient;
        $this->leagueEntityManager->persist($entity);
        $existingRecipients[$recipient] = true;
      }
    }

    $this->leagueEntityManager->flush();
  }
}
