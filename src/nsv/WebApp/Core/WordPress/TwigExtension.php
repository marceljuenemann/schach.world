<?php

namespace Nsv\WebApp\Core\WordPress;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension {

  function getFunctions() {
    return [
      new TwigFunction(
        'get_header',
        $this->bufferedOutput(function() { get_header(); }),
        ['is_safe' => array('html')]
      ),
    ];
  }

  private function bufferedOutput($callback) {
    return function() use ($callback) {
      ob_start();
      call_user_func($callback);
      $data = ob_get_contents();
      ob_end_clean();
      return $data;
    };
  }
}
