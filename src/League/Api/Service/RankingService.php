<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model;
use Nsv\League\Api\Model\Ranking;
use Nsv\League\Api\Model\RankingTeam;
use Nsv\League\Entity\Division;

class RankingService {

  public function __construct() {}

  public function ranking(Division $division, int $round): Ranking {
    $ranking = new Ranking();
    $ranking->teams = array_map(function($team) {
      $rt = new RankingTeam();
      $rt->team = Model\Team::fromEntity($team);
      $rt->rank = 1; // TODO
      $rt->mp = 0; // TODO
      $rt->bp = 0.0; // TODO
      $rt->pairings = []; // TODO
      return $rt;
    }, $division->teams());
    return $ranking;
  }


  /**
   * TODO:
   * - Sort based on Score
   * - Return sorted list
   * - Implement RankingService with rendering
   * - dV 
   * - Berlin tie-break
   * - Org rules
   * 
   * - Future ranking improvements:
   *   - Show reason in frontend
   *   - Show criteria in frontend
   *   - Research and implement rules properly
   *   - Improved API model
   */
}
