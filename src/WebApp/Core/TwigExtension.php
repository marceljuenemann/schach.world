<?php

namespace Nsv\WebApp\Core;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension {

  function getFunctions(): array {
    return [
      new TwigFunction('nsv_md5', function($str) {
        return md5($str);
      })
    ];
  }
}
