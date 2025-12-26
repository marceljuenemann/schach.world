<?php

namespace Nsv\League\Chess\Ranking;

use Nsv\League\Entity\Division;
use Nsv\League\Entity;
use Nsv\League\Entity\Pairing;

/**
 * Ranking for a specific division and round.
 */
// TODO: Rename to Ranking Service.
class Ranking {
  // Board points required for a draw. If null, we just compare board points.
  private int | null $bpForDraw;

  function __construct(public readonly Division $division, public readonly int $round) {
    // TODO: Configure based on organisation rules.
    $this->bpForDraw = $division->boardCount() / 2;
  }

  public function teams(): array {
    return $this->division->teams();
  }



  /**
   * TODO:
   * - Create tests
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

  public function sort() {

    
  }


}
