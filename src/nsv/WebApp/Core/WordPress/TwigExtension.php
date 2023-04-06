<?php

namespace Nsv\WebApp\Core\WordPress;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension {

  function getFunctions() {
    return [
      $this->twigFunction('get_header'),
      $this->twigFunction('get_footer'),
      $this->twigFunction('get_template_part'),
    ];
  }

  private function twigFunction($name) {
    return new TwigFunction($name, function() use ($name) {
      ob_start();
      call_user_func($name, ...func_get_args());
      $data = ob_get_contents();
      ob_end_clean();
      return $data;
    }, ['is_safe' => array('html')]);
  }
}
