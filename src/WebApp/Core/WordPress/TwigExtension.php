<?php

namespace Nsv\WebApp\Core\WordPress;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension {

  function getFunctions() {
    return [
      $this->allowFunction('get_header'),
      $this->allowFunction('get_footer'),
      $this->allowFunction('get_template_part'),

      new TwigFunction('set_title', function($title) {
        add_filter('document_title_parts', function($data) use ($title) {
          $data['title'] = $title;
          return $data;
        });
      })
    ];
  }

  // TODO: Use TwigUtils.
  private function allowFunction($name) {
    return new TwigFunction($name, function() use ($name) {
      ob_start();
      call_user_func($name, ...func_get_args());
      $data = ob_get_contents();
      ob_end_clean();
      return $data;
    }, ['is_safe' => array('html')]);
  }
}
