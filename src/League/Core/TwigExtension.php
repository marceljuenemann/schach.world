<?php

namespace Nsv\League\Core;

use Nsv\League\Core\Encoding;
use Nsv\Util\TwigFunctions;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension {

  public function __construct(private Encoding $encoding) {

  }


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

  public function getFilters()
  {
    return array(
      new TwigFilter('unescape_html_entity', [$this, 'unescape_html_entity']),
    );
  }

  public function unescape_html_entity($value)
  {
    return $this->encoding->utf8_decode(html_entity_decode($value));
  }
}
