<?php

namespace Nsv\League\Core;

/**
 * Utils for character and text encoding.
 */
class Encoding
{
  // TODO: Move everything to Unicode
  const CHARSET = 'ISO-8859-1';
  const UNICODE_ENABLED = false;
  const REMIS = '½';

  /**
   * The character for remis.
   */
  public static function remis() {
    return self::utf8_decode(self::REMIS);
  }

  /**
   * Converts from UTF-8 to the application charset.
   */
  public static function utf8_decode($str) {
    return mb_convert_encoding($str, self::CHARSET, 'UTF-8');
  }

  /**
   * Converts from the application charset to UTF-8.
   */
  public static function utf8_encode($str) {
    return mb_convert_encoding($str, 'UTF-8', self::CHARSET);
  }

  /**
   * The character for remis.
   */
  public static function formatResult(float $result) {
    return str_replace ( ".5", self::remis(), $result == 0.5 ? self::remis() : "$result" );
  }
}
