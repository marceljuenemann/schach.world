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

  public function toLegacyFormat(): array {
    $result[] = $this->rank.'.';
    $result[] = [
      'text' => $this->team->name,
      'url' => '?mannschaft='.$this->team->id,
      'title' => 'Zur Mannschaftsaufstellung'
    ];
    foreach ($this->pairings as $pairings) {
      $result[] = array_map([RankingTeam::class, 'pairingToLegacyFormat'], $pairings);
    }
    $result[] = $this->mp;
    $result[] = (string) $this->bp;
    $result[] = "";
    return $result;
  }

  static function pairingToLegacyFormat(TeamPairing $pairing): array {
    return [
      // TODO: Generate URL in the legacy format.
      'url' => $pairing->uri,
      'title' => 'gegen '.$pairing->opponent->name,
      'text' => Result::format($pairing->score)
    ];
  }
}
