<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model;
use Nsv\League\Api\Model\Ranking;
use Nsv\League\Api\Model\RankingTeam;
use Nsv\League\Api\Model\TeamPairing;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\Pairing;

class RankingService {

  public function __construct() {}

  public function ranking(Division $division, int $round): Ranking {
    if (!$division->config('showRanking')) {
      return new Ranking();
    }

    $teams = $division->teams();
    $pairings = array_filter(iterator_to_array($division->pairings), function (Pairing $pairing) use ($round) {
      // TODO: Filter pairings involving teams not in the division.
      return $pairing->round <= $round &&
        $pairing->result1 !== null && 
        $pairing->result2 !== null;
    });

    $ranking = $this->createModel($division, $teams, $pairings);



    return $ranking;
  }

  /**
   * Creates a basic Ranking model from sorted teams and pairings.
   * 
   * Note that this is partially filled model, some fields still need to be filled.
   */
  private function createModel(Division $division, array $sortedTeams, array $pairings): Ranking {
    $ranking = new Ranking();
    foreach ($sortedTeams as $team) {
      $rt = new RankingTeam();
      $rt->team = Model\Team::fromEntity($team);
      $rt->rank = 1; // TODO
      $rt->mp = 0; // TODO
      $rt->bp = 0.0; // TODO
      $rt->pairings = array_map(fn($team) => [], $sortedTeams);

      $ranking->teams[] = $rt;
    }

    $teamIds = array_map(fn($rt) => $rt->team->id, $ranking->teams);
    foreach ($pairings as $pairing) {
      $index1 = array_search($pairing->team1->id, $teamIds);
      $index2 = array_search($pairing->team2->id,  $teamIds);
      $ranking->teams[$index1]->pairings[$index2][] = TeamPairing::forTeam($pairing->team1, $pairing);
      $ranking->teams[$index2]->pairings[$index1][] = TeamPairing::forTeam($pairing->team2, $pairing);
    }

    $ranking->teamsPromoted = $division->config('teamsPromoted') ?? 0;
    $ranking->teamsDemoted = $division->config('teamsDemoted') ?? 0;
    $ranking->teamsMaybePromoted = $division->config('teamsMaybePromoted') ?? 0;
    $ranking->teamsMaybeDemoted = $division->config('teamsMaybeDemoted') ?? 0;
    return $ranking;
  }
}
