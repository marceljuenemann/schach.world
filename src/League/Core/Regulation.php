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
   * TODO: unit test
   * TODO: Special case for Pokal.
   */
  public static function isWhiteGame(bool $isHomeGame, int $board, League $league) {
    // Default: Home team has black on the first board, subsequent boards alternate.
    return $isHomeGame === ($board % 2 === 0);
  }
}