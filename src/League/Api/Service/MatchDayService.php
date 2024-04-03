<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Api\Model\Pairing;
use Nsv\League\Api\Model\Player;
use Nsv\League\Api\Service\RankingService;
use Nsv\League\Entity;
use Nsv\League\Entity\CacheEntry;
use Nsv\League\Repository\CacheRepository;
use Nsv\League\Repository\PairingRepository;
use Nsv\League\Repository\PlayerRepository;

/**
 * Provides data and operations for the match day view.
 */
class MatchDayService
{
  function __construct(
    private PairingRepository $pairingRepository,
    private PlayerRepository $playerRepository,
    private CacheRepository $cacheRepository,
    private RankingService $rankingService
  ) {}


  public function matchDay(Entity\Division $division, int $round, callable $legacyRanking) {
    $rankingService = $this->rankingService;
    $teams_division = $rankingService->teamsWithPairings($division, $round);

    $model = MatchDay::fromRound($division->round($round));
    $model->legacyRanking = $legacyRanking();

    $pairings = $this->pairingRepository->findByRound($division, $round);
    foreach ($pairings as $pairing) {
      $model->pairings[] = Pairing::fromEntityWithGames($pairing);
      if (!isset($model->lastModified) || $pairing->lastModified > $model->lastModified) {
        $model->lastModified = $pairing->lastModified;
      }
    }

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

    foreach ($division->roundsWithPairing() as $roundObj) {
      $roundModel = MatchDay::fromRound($roundObj);
      if ($division->config('showNextMatchDay') && $roundObj->round == $round + 1) {
        foreach ($roundObj->pairings() as $pairing) {
          $roundModel->pairings[] = Pairing::fromEntity($pairing);
        }
      }
      $model->allRounds[$roundModel->round] = $roundModel;
    }

    // $model->generatedAt = date('Y-m-d H:i:s');
    return $model;
  }

  public function matchDayCached(Entity\Division $division, int $round, callable $legacyRanking) {
    $callback = function() use ($division, $round, $legacyRanking) {
      return $this->matchDay($division, $round, $legacyRanking);
    };
    return $this->cacheRepository->getOrCompute(CacheEntry::TYPE_MATCH_DAY, $division, $round, $callback);
  }
}
