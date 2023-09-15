<?php

namespace Nsv\Util\Pdf;

/**
 * Floating text that automatically wraps when the margin is reached.
 */
class Text extends Element {

  function __construct(private $text) {}

  public function desiredWidth(Pdf $pdf): float|null {
    return null;
  }

  public function render(Pdf $pdf) {
    $pdf->write($pdf->lineHeight, $this->text);
  }
}
