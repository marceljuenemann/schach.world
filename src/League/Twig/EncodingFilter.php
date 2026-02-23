<?php

namespace Nsv\League\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Nsv\League\Core\Encoding;

class EncodingFilter extends AbstractExtension {
  public function getFilters(): array {
    return [
      new TwigFilter('utf8_to_iso', [$this, 'utf8ToIso']),
    ];
  }

  public function utf8ToIso(string $string): string {
    return Encoding::utf8_decode($string);
  }

}