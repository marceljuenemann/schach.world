<?php

namespace Nsv\League\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\IsewaseDwzCalculator;
use Nsv\League\Api\Model\Player;
use Nsv\League\Api\Model\Team;
use Nsv\League\Api\Request\CreateOrUpdatePlayerRequest;
use Nsv\League\Core\Result;
use Nsv\League\Entity;
use Nsv\League\Repository\GameRepository;
use Nsv\League\Repository\PlayerRepository;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlayerService
{
  function __construct(
    private PlayerRepository $playerRepository,
    private GameRepository $gameRepository,
    private IsewaseDwzCalculator $dwzCalculator,
    private EntityManagerInterface $leagueEntityManager
  ) {}

  // TODO: cache, especially for the DWZ calculation.
  public function player(Entity\League $league, int $playerId): Player {
    $player = $this->playerRepository->find($playerId);
    if ($player->team->league != $league) {
      throw new NotFoundHttpException("Player not found");
    }

    $result = Player::fromEntity($player);
    $result->team = Team::fromEntity($player->team);
    foreach ($this->gameRepository->findByPlayer($player) as $game) {
      $result->addGame($game);
    }
    if (isset($result->games)) {
      $result->dwzCalculation = $this->dwzCalc($result, $player->yearOfBirth());
    }
    return $result;
  }

  private function dwzCalc(Player $player, int|null $yearOfBirth): array|null {
    $opponentDwz = array();
    $points = 0.0;
    foreach ($player->games as $game) {
      // Only count actually played games against opponents with DWZ.
      if (Result::wasPlayed($game->result) && $game->opponentPlayer->dwz) {
        $opponentDwz[] = $game->opponentPlayer->dwz;
        $points += Result::score($game->result);
      }
    }
    try {
      return $this->dwzCalculator->calculate($player->dwz, $opponentDwz, $points, $yearOfBirth);
    } catch (\Exception $e) {
      // Swallow timeouts and server errors.
      return null;
    }
  }

  public function createPlayer(Entity\Team $team, CreateOrUpdatePlayerRequest $request): Entity\Player {
    $player = new Entity\Player();
    $player->team = $team;
    $player->number = $this->nextBoardNumber($team);
    $this->applyRequest($player, $request, null);
    $this->leagueEntityManager->persist($player);
    $this->leagueEntityManager->flush();
    return $player;
  }

  public function updatePlayer(Entity\Player $player, CreateOrUpdatePlayerRequest $request): Entity\Player {
    $this->applyRequest($player, $request, $player->lateRegistrationDivision);
    $this->leagueEntityManager->persist($player);
    $this->leagueEntityManager->flush();
    return $player;
  }

  private function applyRequest(Entity\Player $player, CreateOrUpdatePlayerRequest $request, ?Entity\Division $existingLateRegistrationDivision): void {
    $player->firstName = $request->firstName ?? '';
    $player->lastName = $request->lastName;
    $player->title = $request->title ?? '';
    $player->zps = $request->zps ?? '';
    $player->dwz = $request->dwz;
    $player->elo = $request->elo;
    $player->birth = $request->yearOfBirth ? (string) $request->yearOfBirth : '';
    $player->gender = $request->gender ? strtolower($request->gender) : '';
    $player->lateRegistrationRound = $request->lateRegistrationRound;

    if ($request->lateRegistrationRound === null) {
      $player->lateRegistrationDivision = null;
    } else {
      $player->lateRegistrationDivision = $existingLateRegistrationDivision ?? $player->team->division;
    }
  }

  public function deletePlayer(Entity\Player $player): void {
    if (count($this->gameRepository->findByPlayer($player)) > 0) {
      throw new ConflictHttpException('Player has already played games and cannot be deleted.');
    }

    $team = $player->team;
    $number = $player->number;
    $this->leagueEntityManager->remove($player);
    $this->leagueEntityManager->flush();

    // Shift down board numbers of remaining players, mirroring legacy stafspie.php's
    // "UPDATE spieler SET brettnr=brettnr-1 WHERE mannschaft=? AND brettnr>?".
    $this->leagueEntityManager->createQuery(
      'UPDATE Nsv\League\Entity\Player p SET p.number = p.number - 1 WHERE p.team = :team AND p.number > :number'
    )->setParameter('team', $team)
     ->setParameter('number', $number)
     ->execute();
  }

  // Mirrors legacy SED_Spieler::getNextBrettNr(): max existing board number + 1,
  // or 1 for an empty team - unless the league is configured for three-digit
  // numbers (League::$configPlayerNumbersWithTeamNumber, column spielDreistelligeNr),
  // in which case an empty team's first player starts at team-number * 100 + 1.
  private function nextBoardNumber(Entity\Team $team): int {
    $max = 0;
    foreach ($team->players as $existing) {
      $max = max($max, $existing->number);
    }
    if ($max > 0) return $max + 1;
    return $team->league->configPlayerNumbersWithTeamNumber ? $team->number * 100 + 1 : 1;
  }
}
