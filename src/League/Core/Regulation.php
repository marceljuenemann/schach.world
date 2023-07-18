<?php

namespace Nsv\League\Core;

use Nsv\League\Entity\League;

/**
 * Implements part of the chess regulations that differ between organizations. This is
 * where we do a bunch of hacks :)
 */
class Regulation
{
  /**
   * Determines whether a player had white based on the board number.
   * 
   * TODO: Special case for Pokal.
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
}