<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model;
use Nsv\League\Api\Model\Ranking;
use Nsv\League\Api\Model\RankingTeam;
use Nsv\League\Entity\Division;

class RankingService {

  public function __construct() {}

  public function ranking(Division $division, int $round): Ranking {
    $teams = $division->teams();
    $ranking = new Ranking();
    $ranking->teams = array_map(function($team) use ($teams) {
      $rt = new RankingTeam();
      $rt->team = Model\Team::fromEntity($team);
      $rt->rank = 1; // TODO
      $rt->mp = 0; // TODO
      $rt->bp = 0.0; // TODO

      // Fill in pairings for eaach team.
      $rt->pairings = array_map(fn($team) => [], $teams); // TODO
      return $rt;
    }, $teams);

    $ranking->teamsPromoted = $division->config('teamsPromoted') ?? 0;
    $ranking->teamsDemoted = $division->config('teamsDemoted') ?? 0;
    $ranking->teamsMaybePromoted = $division->config('teamsMaybePromoted') ?? 0;
    $ranking->teamsMaybeDemoted = $division->config('teamsMaybeDemoted') ?? 0;

    return $ranking;
  }
}
