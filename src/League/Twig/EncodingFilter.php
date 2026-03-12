<?php

namespace Nsv\League\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Nsv\League\Core\Encoding;

class EncodingFilter extends AbstractExtension {
  public function getFilters(): array {
    return [
      new TwigFilter('utf8_decode', [$this, 'utf8Decode']),
    ];
  }

  public function utf8Decode(string $string): string {
    return Encoding::utf8_decode($string);

  }

}