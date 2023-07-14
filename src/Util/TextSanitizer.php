<?php

namespace Nsv\Util;

class TextSanitizer {

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
