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
   * Recursively converts all strings in an object to UTF-8.
   */
  public static function deep_utf8_encode($obj) {
    return self::deep_walk($obj, function(&$val) {
      if (is_string($val)) {
        $val = self::utf8_encode($val);
      }
    });
  }

  /**
   * Recursively converts all strings in an object from UTF-8 to CHARSET.
   */
  public static function deep_utf8_decode($obj) {
    return self::deep_walk($obj, function(&$val) {
      if (is_string($val)) {
        $val = self::utf8_decode($val);
      }
    });
  }

  private static function deep_walk(&$obj, $callback) {
    if (is_array($obj) || is_object($obj)) {
      foreach ($obj as $key => &$val) {
        self::deep_walk($val, $callback);
      }
    } else {
      $callback($obj);
    }
  }
}
