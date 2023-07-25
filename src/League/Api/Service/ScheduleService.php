<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Model\Division;
use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Api\Model\Pairing;
use Nsv\League\Entity;
use Nsv\League\Repository\PairingRepository;

class ScheduleService
{
  function __construct(private PairingRepository $pairingRepository) {}

  /**
   * Returns all dates configured for this league and its divisions.
   * 
   * @return array of date strings, sorted 
   */
  public function leagueDates(Entity\League $league) {
    $allDates = array_unique(array_map(function(Entity\Date $date) {
      return $date->date;
    }, $league->dates()->toArray()));
    sort($allDates);
    return $allDates;
  }

  /**
   * Returns the date closest to the given date.
   * 
   * @param dates the dates to look through as date string
   * @param date the date to be closest to
   * @return the closest date string or null if no dates were given
   */
  public function closestDate(array $dates, string $date): string|null {
    if (!count($dates)) return null;
    $date = date_create($date);
    $closestDate = null;
    $closestDiff = null;
    foreach ($dates as $candiDate) {
      $interval = date_diff(date_create($candiDate), $date);
      $diff = (int) $interval->format('%R%a'); // +/- number of days
      if ($closestDiff === null || abs($diff) < abs($closestDiff)) {
        $closestDate = $candiDate;
        $closestDiff = $diff;
      }
    }
    return $closestDate;
  }

  /**
   * Returns an overview of matches in the league for the given date.
   * 
   * @param league the league for which to generate the overview
   * @param date the date for which to show games
   * @return array with divisionId => Division model with match days 
   */
  // TODO: Write tests
  public function matchesByDate(Entity\League $league, string $date): array {
    // Determine rounds to return for each division.
    $result = [];
    $roundsToFetch = [];
    foreach ($league->divisions as $division) {
      $result[$division->id] = Division::fromEntity($division);
      foreach ($division->roundsOnDate($date) as $round) {
        $roundsToFetch[] = $round;
        $result[$division->id]->matchDays[$round->round] = MatchDay::fromRound($round);
      }
    }

    // Fetch relevant matches.
    $pairings = $this->pairingRepository->findByRounds($roundsToFetch);
    foreach ($pairings as $pairing) {
      $matchDay = $result[$pairing->division->id]->matchDays[$pairing->round];
      $matchDay->pairings[] = Pairing::fromEntity($pairing);
    }
    return $result;
  }

  /**
   * Returns all match days for a specific division.
   */
  // TODO: rename to divisionSchedule
  public function matchDays(Entity\Division $division): array {
    $matchDays = [];
    $dates = $division->dates();
    foreach ($division->pairings as $pairing) {
      if (!isset($matchDays[$pairing->round])) {
        $date = isset($dates[$pairing->round]) ? $dates[$pairing->round]->date : null;
        $matchDays[$pairing->round] = MatchDay::create($division, $pairing->round, $date);
      }
      $matchDays[$pairing->round]->pairings[] = Pairing::fromEntity($pairing);
    }
    usort($matchDays, [MatchDay::class, 'compare']);
    return array_values($matchDays);
  }
}
