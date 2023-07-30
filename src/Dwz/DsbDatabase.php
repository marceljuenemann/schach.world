<?php

namespace Nsv\Dwz;

/**
 * Utils for interaction with the DSB (German chess federation) rating database.
 */
class DsbDatabase
{
  // TODO: Maybe move to a Zps class with ZPS utils?
  const ZPS_CLUB_LENGTH = 5;

  const PLAYER_RECORD_URI = "https://www.schachbund.de/spieler.html?zps=";

  /**
   * Returns link to the player record on the DSB website.
   */
  public static function playerRecordUri($fullZps) {
    return self::PLAYER_RECORD_URI . $fullZps;
  }
}
