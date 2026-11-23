<?php

namespace Nsv\League\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Api\Request\LateRegistrationRequest;
use Nsv\League\Api\Request\UpdatePairingRequest;
use Nsv\League\Entity;
use Nsv\WebApp\Core\NsvErrorException;

class PairingService
{
  const SIDES = [1, 2];

  function __construct(
    private PlayerService $playerService,
    private EntityManagerInterface $leagueEntityManager
  ) {}

  /**
   * Stores the result of a pairing, replacing all of its games.
   *
   * The tables are MyISAM, so a save that fails halfway can't be rolled back. Everything
   * therefore needs to be validated before the first write.
   */
  public function updatePairing(Entity\Pairing $pairing, UpdatePairingRequest $request): void {
    $this->validateResults($pairing, $request);
    $players = [];
    foreach (self::SIDES as $side) {
      $players[$side] = $this->eligiblePlayers($pairing, $side);
      $this->validateSide($pairing, $request, $side, $players[$side]);
    }

    // Creating a player flushes, so this has to happen before touching the games.
    $lateRegistrations = [];
    foreach (self::SIDES as $side) {
      $lateRegistrations[$side] = $this->createLateRegistrations($pairing, $request, $side);
    }

    foreach ($pairing->games as $game) {
      $this->leagueEntityManager->remove($game);
    }
    foreach ($request->games as $gameRequest) {
      $game = new Entity\Game();
      $game->pairing = $pairing;
      $game->board = $gameRequest->board;
      foreach (self::SIDES as $side) {
        $playerId = $gameRequest->{"player$side"};
        $game->{"player$side"} = $lateRegistrations[$side][$gameRequest->board]
          ?? ($playerId === null ? null : $players[$side][$playerId]);
        $game->{"result$side"} = $gameRequest->{"result$side"};
      }
      $this->leagueEntityManager->persist($game);
    }

    $comment = trim($request->comment ?? '');
    $pairing->result1 = $request->result1;
    $pairing->result2 = $request->result2;
    $pairing->comment = $comment === '' ? null : $comment;
    $pairing->lastModified = date('Y-m-d H:i:s');
    $this->leagueEntityManager->flush();

    // TODO: Send the confirmation email if $request->sendConfirmation is set.
  }

  private function validateResults(Entity\Pairing $pairing, UpdatePairingRequest $request): void {
    $boardCount = $pairing->division->boardCount();
    $boards = array_map(fn($game) => $game->board, $request->games);
    sort($boards);
    if ($boards !== range(1, $boardCount)) {
      throw new NsvErrorException("Die Paarung muss genau $boardCount Bretter haben.");
    }
    if ($request->result1 > $boardCount || $request->result2 > $boardCount) {
      throw new NsvErrorException("Das Gesamtergebnis darf höchstens $boardCount betragen.");
    }
  }

  private function validateSide(Entity\Pairing $pairing, UpdatePairingRequest $request, int $side, array $eligiblePlayers): void {
    $seen = [];
    foreach ($request->games as $game) {
      $playerId = $game->{"player$side"};
      $lateRegistration = $game->{"lateRegistration$side"};
      if ($playerId === null && $lateRegistration === null) continue;

      if ($playerId !== null && $lateRegistration !== null) {
        throw new NsvErrorException("Brett {$game->board}: Es kann nur eine Person ausgewählt werden.");
      }
      if ($playerId !== null && !isset($eligiblePlayers[$playerId])) {
        throw new NsvErrorException("Brett {$game->board}: Die Person steht nicht in der Aufstellung.");
      }
      if ($lateRegistration !== null) {
        $team = $this->lateRegistrationTeam($pairing->{"team$side"}, $lateRegistration);
        if (!$team) {
          throw new NsvErrorException("Brett {$game->board}: Die Nachmeldung ist für diese Mannschaft nicht möglich.");
        }
        if ($this->isOnTeam($team, $lateRegistration)) {
          throw new NsvErrorException("Brett {$game->board}: Die Nachmeldung steht bereits in der Aufstellung.");
        }
      }

      $key = $lateRegistration ? $this->lateRegistrationKey($lateRegistration) : $playerId;
      if (isset($seen[$key])) {
        throw new NsvErrorException("Brett {$game->board}: Die Person spielt bereits an einem anderen Brett.");
      }
      $seen[$key] = true;
    }
  }

  /**
   * Returns the teams whose players may play for the given team.
   */
  private function eligibleTeams(Entity\Team $team): array {
    return [$team, ...$team->substituteTeams()];
  }

  /**
   * Returns the players that may be selected for one side of the pairing, by ID. This
   * includes the current players of the pairing even if they have left the team since,
   * as the pairing could not be edited anymore otherwise.
   */
  private function eligiblePlayers(Entity\Pairing $pairing, int $side): array {
    $players = [];
    foreach ($this->eligibleTeams($pairing->{"team$side"}) as $team) {
      foreach ($team->players as $player) {
        $players[$player->id] = $player;
      }
    }
    foreach ($pairing->games as $game) {
      $player = $game->{"player$side"};
      if ($player) {
        $players[$player->id] = $player;
      }
    }
    return $players;
  }

  private function lateRegistrationTeam(Entity\Team $team, LateRegistrationRequest $lateRegistration): Entity\Team|null {
    foreach ($this->eligibleTeams($team) as $eligibleTeam) {
      if ($eligibleTeam->number === $lateRegistration->teamNumber) {
        return $eligibleTeam;
      }
    }
    return null;
  }

  private function isOnTeam(Entity\Team $team, LateRegistrationRequest $lateRegistration): bool {
    if (!$lateRegistration->player->zps) return false;
    foreach ($team->players as $player) {
      if ($player->zps === $lateRegistration->player->zps) return true;
    }
    return false;
  }

  /**
   * Identifies a late registration, so that it can't be used on multiple boards.
   */
  private function lateRegistrationKey(LateRegistrationRequest $lateRegistration): string {
    $player = $lateRegistration->player;
    return 'late-' . ($player->zps ?: "{$player->lastName},{$player->firstName}");
  }

  /**
   * Creates the players of all late registrations of one side and returns them by board.
   */
  private function createLateRegistrations(Entity\Pairing $pairing, UpdatePairingRequest $request, int $side): array {
    $players = [];
    foreach ($request->games as $game) {
      $lateRegistration = $game->{"lateRegistration$side"};
      if ($lateRegistration === null) continue;

      $lateRegistration->player->lateRegistrationRound = $pairing->round;
      $team = $this->lateRegistrationTeam($pairing->{"team$side"}, $lateRegistration);
      $players[$game->board] = $this->playerService->createPlayer($team, $lateRegistration->player, $pairing->division);
    }
    return $players;
  }
}
