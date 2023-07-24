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
  public function leagueOverview(Entity\League $league, string $date, bool $exactDate) {
    // TODO: Write tests
    $result = new \stdClass();

    // Determine rounds to return for each division.
    $roundsToFetch = [];
    foreach ($league->divisions as $division) {
      $model = Division::fromEntity($division);
      $result->divisions[$model->id] = $model;
      foreach ($this->roundsForDivision($division, $date, $exactDate) as $round) {
        $roundsToFetch[] = $round;
        $result->datesShown[] = $round->date;
        // TODO: MatchDay::fromRound()
        $md = new MatchDay();
        $md->round = $round->round;
        $md->date = $round->date;
        $md->uri = $division->matchDayUri($md->round);  // TODO: $round->uri()
        $model->matchDays[$md->round] = $md;
      }
    }
    $result->datesShown = array_unique($result->datesShown);

    // Fetch relevant matches.
    $pairings = $this->pairingRepository->findByRounds($roundsToFetch);
    foreach ($pairings as $pairing) {
      $matchDay = $result->divisions[$pairing->division->id]->matchDays[$pairing->round];
      $matchDay->pairings[] = Pairing::fromEntity($pairing);
    }

    // List all match dates for this league.
    $result->allDates = array_unique(array_map(function(Entity\Date $date) {
      return $date->date;
    }, $league->dates()->toArray()));
    sort($result->allDates);

    return $result;
  }

  /**
   * Returns the Date entities to show in the overview for this division.
   */
  private function roundsForDivision(Entity\Division $division, string $date, bool $exactDate) {
    // Determine the date to show.
    if (!$exactDate) {
      $closestDate = $division->closestMatchDate($date);
      if ($closestDate) {
        $date = $closestDate->date;
      }
    }

    return $division->roundsOnDate($date);
    /*
    // If no match dates were found yet and not an exact match, return first round. 
    if (!count($matchDays) && !$exactDate) {
      // TODO: Return first round
    }

    return $matchDays;
    */
  }
}
