<?php

namespace Nsv\Util;

class TextSanitizer {

  // TODO: Move to UTF-8 when migrating the league manager.
  public const CHARSET = 'iso-8859-1';

  /**
   * Calls htmlspecialchars with the correct character encoding.
   */
  public static function html($str) {
    return htmlspecialchars($str, ENT_QUOTES|ENT_SUBSTITUTE, TextSanitizer::CHARSET);
  }

  /**
   * Generates a lower-case alphanumberic string (with dashes) from an arbitrary string.
   * This is similar to WordPress' sanitize_title and intended to be generate readable URLs. 
   */
  public static function slug($str) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', TextSanitizer::replaceUmlauts($str))));
  }

  public static function replaceUmlauts($str) {
    // TODO: better method. Or replace when no longer needed
    $str = str_replace(
      array(utf8_decode('ä'), utf8_decode('ö'), utf8_decode('ü'), utf8_decode('ß')),
      array('ae', 'oe', 'ue', 'ss'),
      $str
    );
    $str = str_replace(
      array(('ä'), ('ö'), ('ü'), ('ß')),
      array('ae', 'oe', 'ue', 'ss'),
      $str
    );
    return $str;
  }
}
