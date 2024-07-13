<?php

namespace Nsv\WebApp\Core;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension {

  function __construct(private NsvJs $nsvJs) {}

  function getFunctions(): array {
    return [
      new TwigFunction('include_nsv_ng', function() {
        return $this->nsvJs->nsvNgTags();
      }, ['is_safe' => array('html')]),

      new TwigFunction('nsv_js_src', function() {
        return $this->nsvJs->scriptUrl();
      }),

      new TwigFunction('nsv_md5', function($str) {
        return md5($str);
      }),
      
      new TwigFunction('stacktrace', function($e) {
        return PHP_EOL . '<pre>' . PHP_EOL . $e->getTraceAsString() . PHP_EOL . '</pre>' . PHP_EOL;
      })
    ];
  }
}
