<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Api\Model\Pairing;
use Nsv\League\Entity;

/**
 * Manages the schedule for a division.
 */
class ScheduleService
{
  public function matchDays(Entity\Division $division): array {
    // TODO: this deserves as unit test.
    $matchDays = [];
    $dates = $division->dates();
    foreach ($division->pairings as $pairing) {
      if (!isset($matchDays[$pairing->round])) {
        $md = new MatchDay();
        $md->round = $pairing->round;
        $md->date = isset($dates[$md->round]) ? $dates[$md->round] : null;
        $md->uri = $division->matchDayUri($md->round);
        $matchDays[$pairing->round] = $md;
      }
      $matchDays[$pairing->round]->pairings[] = Pairing::fromEntity($pairing);
    }
    usort($matchDays, [MatchDay::class, 'compare']);
    return array_values($matchDays);
  }
}
