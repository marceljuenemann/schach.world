<?php

namespace Nsv\League\Core;

use Nsv\Util\TwigFunctions;
use Twig\Extension\AbstractExtension;

class TwigExtension extends AbstractExtension {

  function getFunctions(): array {
    return [
      TwigFunctions::outputBuffering('legacy_league_header', function($title, $isHomescreen) {
        global $globals;
        $globals['isHomescreen'] = $isHomescreen;  // hint for navigation bar.
        include_once('gui.inc.php');
      }),

      TwigFunctions::outputBuffering('legacy_league_footer', 'SED_GUIclose'),
    ];
  }
}
