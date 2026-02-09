<?php

namespace Nsv\Util;

function _utf8_decode($str) {
  return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
}

class TextSanitizer {

  /**
   * Generates a lower-case alphanumberic string (with dashes) from an arbitrary string.
   * This is similar to WordPress' sanitize_title and intended to be generate readable URLs. 
   */
  public static function slug($str) {
    $str = trim($str);
    $str = strtolower($str);
    $str = TextSanitizer::replaceUmlauts($str);
    $str = preg_replace('/[^A-Za-z0-9-]+/', '-', $str);
    return $str;
  }

  public static function replaceUmlauts($str) {
    // TODO: better method. Or replace when no longer needed
    $str = str_replace(
      array(_utf8_decode('ä'), _utf8_decode('ö'), _utf8_decode('ü'), _utf8_decode('ß')),
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
