<?php

namespace Nsv\League\Core;

/**
 * Utils for character and text encoding.
 */
class Encoding
{
  // TODO: Move everything to Unicode
  const UNICODE_ENABLED = true;
  const CHARSET = self::UNICODE_ENABLED ? 'UTF-8' : 'ISO-8859-1';

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
}
