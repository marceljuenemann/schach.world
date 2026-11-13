<?php

namespace Nsv\Dwz\Calculation;

/**
 * Reasons why no new DWZ could be calculated for a player.
 */
enum DwzUnavailableReason: string
{
  /** The player has not played any games against rated opponents yet. */
  case NoRatedGames = 'no-rated-games';

  /** Unrated players need at least 5 games against rated opponents for a first rating (WO 3.4.1.1). */
  case TooFewGamesForFirstRating = 'too-few-games-for-first-rating';

  /** Unrated players need at least half a point against rated opponents for a first rating (WO 3.4.1.1). */
  case NoPointsForFirstRating = 'no-points-for-first-rating';
}
