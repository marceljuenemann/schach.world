<?php

namespace Nsv\WebApp\Core;

use Nsv\Util\TextSanitizer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension {

  function getFunctions() {
    return [
      /**
       * Outputs a link.
       * 
       * @param mixed uri the URI to link to or an object implementing Linkable.
       * @param string|null title to show on the link 
       */
      new TwigFunction('nsv_link', function($target, $title = null) {
        if ($target instanceof Linkable) {
          $title = $target->linkTitle();
          $target = $target->linkUri();
        }
        return "<a href='$target'>" . TextSanitizer::html($title) . "</a>";
      }, ["is_safe" => array('html')])
    ];
  }
}
