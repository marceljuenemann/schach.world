<?php

namespace Nsv\Util;

use Twig\TwigFunction;

class TwigFunctions {

  /**
   * Returns a TwigFunction that returns the output of the given callback function.
   */
  public static function outputBuffering($name, $callback) {
    return new TwigFunction($name, function() use ($callback) {
      ob_start();
      call_user_func($callback, ...func_get_args());
      $data = ob_get_contents();
      ob_end_clean();
      return $data;
    }, ['is_safe' => array('html')]);
  }
}
