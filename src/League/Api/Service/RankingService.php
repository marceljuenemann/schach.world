<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model;
use Nsv\League\Api\Model\Ranking;
use Nsv\League\Api\Model\RankingTeam;
use Nsv\League\Api\Model\TeamPairing;
use Nsv\League\Api\Service\TieBreak\Berlin;
use Nsv\League\Api\Service\TieBreak\HeadToHead;
use Nsv\League\Api\Service\TieBreak\Scores;
use Nsv\League\Api\Service\TieBreak\TieBreaks;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\Pairing;

class RankingService {

  public function __construct() {}

  public function ranking(Division $division, int $round): Ranking {
    if (!$division->config('showRanking')) {
      return new Ranking();
    }

    // COVID Hack: Always show full ranking as rounds were mixed up.
    // TODO: Remove this special case as it doesn't affect the final standing.
    if ($division->league->year == 2021) {
      $round = 99;
    }

    $teams = $division->teams();
    $pairings = array_filter(iterator_to_array($division->pairings), function (Pairing $pairing) use ($division, $round) {
      return $pairing->round <= $round &&
        $pairing->result1 !== null && 
        $pairing->result2 !== null &&
        $pairing->team1 !== null &&
        $pairing->team2 !== null &&
        $pairing->team1->division == $division &&
        $pairing->team2->division == $division;
    });

    // Sort teams using the defined tiebreak criteria.
    // TODO: Make tie breaks more configurable.
    $scores = new Scores($division, $pairings);
    $tiebreaks = new TieBreaks([
      $scores,
      new HeadToHead($scores),
      new Berlin($scores)
    ]);
    $tiebreaks->sort($teams);

    // Create the ranking model.
    // TODO: Add details about the tie break.
    $ranking = $this->createModel($division, $teams, $pairings);
    for ($i = 0; $i < count($ranking->teams); $i++) {
      $team = $ranking->teams[$i];
      $team->mp = $scores->matchPoints($team->team->id);
      $team->bp = $scores->boardPoints($team->team->id);

      // Determine rank, taking ties into account.
      if ($i > 0 && $tiebreaks->compare($teams[$i], $teams[$i - 1]) === 0) {
        $team->rank = $ranking->teams[$i - 1]->rank;
      } else {
        $team->rank = $i + 1;
      }
    }

    return $ranking;
  }

  /**
   * Creates a basic Ranking model from sorted teams and pairings.
   * 
   * Note that this is a partially filled model, some fields still need to be filled.
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

    // TODO: Add these flags to RankingTeam directly.
    $ranking->teamsPromoted = $division->config('teamsPromoted') ?? 0;
    $ranking->teamsDemoted = $division->config('teamsDemoted') ?? 0;
    $ranking->teamsMaybePromoted = $division->config('teamsMaybePromoted') ?? 0;
    $ranking->teamsMaybeDemoted = $division->config('teamsMaybeDemoted') ?? 0;
    return $ranking;
  }
}
