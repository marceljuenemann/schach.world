<?php

namespace Nsv\Dwz;

/**
 * Utils for interaction with the DSB (German chess federation) rating database.
 */
class DsbDatabase
{
  // TODO: Maybe move to a Zps class with ZPS utils?
  const ZPS_CLUB_LENGTH = 5;

  const PLAYER_SEARCH_URI = "https://www.schachbund.de/dwz-spieler.html?search="; 
  const CLUB_URI = "https://www.schachbund.de/verein/%s.html";

  /**
   * Returns link to the player search on the DSB website.
   */
  public static function playerSearchUri($name) {
    return self::PLAYER_SEARCH_URI . urlencode($name);
  }

  /**
   * Returns link to the player list of a club.
   */
  public static function clubUri($clubZps) {
    return sprintf(self::CLUB_URI, $clubZps);
  }
}
