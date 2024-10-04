<?php

namespace Nsv\League\Core;

use Nsv\League\Core\Encoding;
use Nsv\League\Core\Result;
use Nsv\Util\TwigFunctions;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

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
  public function getFilters(): array {
    return [
      new TwigFilter('format_float_result', [$this, 'format_float_result']),
    ];
  }

  public function halfPoint() {
    return Encoding::utf8_decode(Result::UNICODE_DRAW);
  }

  /**
   * Format floats as strings. Replace .5 with '½'
   */
  public function format_float_result($float) {
    $floatToString = strval($float);
    if($floatToString == '0.5') {
      $formattedResult = $this->halfPoint();
    } else {
      $formattedResult = str_replace('.5', $this->halfPoint(), $floatToString);
    }
    return $formattedResult;
  }
}
