<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Api\Model\Pairing;
use Nsv\League\Entity;
use Nsv\League\Entity\Date;
use Nsv\League\Entity\Division;

class ScheduleService
{
  /**
   * Returns all match days for a specific division.
   */
  public function matchDays(Entity\Division $division): array {
    $matchDays = [];
    $dates = $division->dates();
    foreach ($division->pairings as $pairing) {
      if (!isset($matchDays[$pairing->round])) {
        $md = new MatchDay();
        $md->round = $pairing->round;
        $md->date = isset($dates[$md->round]) ? $dates[$md->round]->date : null;
        $md->uri = $division->matchDayUri($md->round);
        $matchDays[$pairing->round] = $md;
      }
      $matchDays[$pairing->round]->pairings[] = Pairing::fromEntity($pairing);
    }
    usort($matchDays, [MatchDay::class, 'compare']);
    return array_values($matchDays);
  }

  /**
   * Returns an overview of match dates and matches closest to the given date.
   * 
   * @param league the league for which to generate the overview
   * @param date the date for which to show games. Typically today.
   * @param exactDate whether to only return games of the exact date or of the closest match day.
   */
  public function overview(Entity\League $league, string $date, bool $exactDate) {
    $result = new \stdClass();

    // Fetch and sort all configured match dates.
    $result->allDates = array_unique(array_map(function(Entity\Date $date) {
      return $date->date;
    }, $league->dates()->toArray()));
    sort($result->allDates);

    // Determine rounds to fetch for each division (division => Entity\Date).
    $closestDates = [];
    foreach ($league->divisions as $division) {
      $closestDate = $division->closestMatchDate($date);
      if ($closestDate) {
        $result->closestDates[$division->id] = $closestDate->date;
        $result->closestRounds[$division->id] = $closestDate->round;
      }
      // TODO: handle null
    }

    return $result;
  }
}
