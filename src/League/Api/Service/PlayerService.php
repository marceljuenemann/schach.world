<?php

namespace Nsv\League\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\Calculation\DwzCalculation;
use Nsv\Dwz\Calculation\DwzCalculator;
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
  /** The first season in which the current DWZ rules (Wertungsordnung 2026) apply. */
  const FIRST_SEASON_WITH_DWZ_CALCULATION = 2026;

  function __construct(
    private PlayerRepository $playerRepository,
    private GameRepository $gameRepository,
    private DwzCalculator $dwzCalculator,
    private EntityManagerInterface $leagueEntityManager
  ) {}

  // TODO: cache.
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
    // Earlier seasons were evaluated under the old rules and use ratings from before the DSB's
    // 2026 rescaling, so the calculation would be misleading there.
    if ($league->year >= self::FIRST_SEASON_WITH_DWZ_CALCULATION) {
      // Seasons span two years, and the age is determined by the year in which the tournament ends.
      $result->dwzCalculation = $this->dwzCalc($result, $player->yearOfBirth(), $league->year + 1);
    }
    return $result;
  }

  private function dwzCalc(Player $player, int|null $yearOfBirth, int $evaluationYear): DwzCalculation {
    $opponentDwz = array();
    $points = 0.0;
    foreach ($player->games ?? [] as $game) {
      // Only count actually played games against opponents with DWZ.
      if (Result::wasPlayed($game->result) && $game->opponentPlayer->dwz) {
        $opponentDwz[] = $game->opponentPlayer->dwz;
        $points += Result::score($game->result);
      }
    }
    return $this->dwzCalculator->calculate($player->dwz, $opponentDwz, $points, $yearOfBirth,
        evaluationYear: $evaluationYear);
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
      // Only allow setting a late registration round without the team having a division if
      // the player already had one - e.g. the team's division was removed after the fact.
      $division = $existingLateRegistrationDivision ?? $player->team->division;
      if (!$division) {
        throw new ConflictHttpException('Cannot set a late registration round for a team without a division.');
      }
      $player->lateRegistrationDivision = $division;
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

    // Shift down board numbers of remaining players.
    $this->leagueEntityManager->createQuery(
      'UPDATE Nsv\League\Entity\Player p SET p.number = p.number - 1 WHERE p.team = :team AND p.number > :number'
    )->setParameter('team', $team)
     ->setParameter('number', $number)
     ->execute();
  }

  private function nextBoardNumber(Entity\Team $team): int {
    return $team->firstPlayerNumber() + count($team->players);
  }

  public function reorderPlayers(Entity\Team $team, array $playerIds): void {
    $existingIds = [];
    $playersById = [];
    foreach ($team->players as $player) {
      $existingIds[] = $player->id;
      $playersById[$player->id] = $player;
    }

    // Compare as sorted copies - $playerIds itself must stay in the caller's given
    // order below, sort() would mutate it in place if applied directly.
    $sortedExisting = $existingIds;
    $sortedGiven = $playerIds;
    sort($sortedExisting);
    sort($sortedGiven);
    if ($sortedExisting !== $sortedGiven) {
      throw new ConflictHttpException('Player list does not match the team\'s current players.');
    }

    foreach ($playerIds as $index => $id) {
      $playersById[$id]->number = $team->firstPlayerNumber() + $index;
      $this->leagueEntityManager->persist($playersById[$id]);
    }
    $this->leagueEntityManager->flush();
  }
}
