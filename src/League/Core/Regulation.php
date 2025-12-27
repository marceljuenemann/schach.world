<?php

namespace Nsv\League\Core;

use Nsv\League\Entity\League;

/**
 * Implements part of the chess regulations that differ between organizations. This is
 * where we do a bunch of hacks :)
 */
// TODO: Move to Chess namespace.
class Regulation
{
  /**
   * Determines whether a player had white based on the board number.
   */
  public static function isWhiteGame(bool $isHomeGame, int $board, League $league) {
    // Default: Home team has black on the first board, subsequent boards alternate.
    $isWhite = $isHomeGame === ($board % 2 === 0);

    // NSV Pokal: Black, White, White, Black
    if ($league->organisation === '7p' && $board >= 3) {
      return !$isWhite;
    }
    return $isWhite;
  }

  /**
   * There are two supported ways to calculate match points, which only make a
   * difference when both teams left a board empty (a -:- score):
   * 1. The team with more board points wins the match.
   * 2. Scoring half of the possible board points is a draw, more is a win.
   * 
   * This function returns true if the second algorithm is used.
   */
  public static function hasMatchPointsMinimum(League $league): bool {
    // TODO: Make this configurable rather than hardcoded.
    switch ($league->organisation) {
      case "fbl":
      case "frl":
      case "ndsj":
      case "703":
      case "703j":
      case  "A":
        return true;
      
      case "7":
      case "7p":
        return $league->year > 2014;

      default:
        return false;
    }
  }
}
