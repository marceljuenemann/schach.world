<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Core\Result;

/**
 * Represents a team in a ranking.
 */
class RankingTeam
{
  public int $rank;
  public Team $team;
  public int $mp;
  public float $bp;

  // List of TeamPairings against each opponent in the ranking.
  public array $pairings = [];

  public function toLegacyFormat(int $divisionId): array {
    $result[] = $this->rank.'.';
    $result[] = [
      'text' => $this->team->name,
      'url' => '?mannschaft='.$this->team->id,
      'title' => 'Zur Mannschaftsaufstellung'
    ];
    foreach ($this->pairings as $pairings) {
      $result[] = array_map(function($pairing) use ($divisionId) {
        return [
          'url' => "?staffel=$divisionId&r={$pairing->round}#p{$this->team->id}x{$pairing->opponent->id}",
          'title' => 'gegen '.$pairing->opponent->name,
          'text' => Result::format($pairing->score)
        ];
      }, $pairings);
    }
    $result[] = $this->mp;
    $result[] = Result::format($this->bp);
    $result[] = "";
    return $result;
  }
}
