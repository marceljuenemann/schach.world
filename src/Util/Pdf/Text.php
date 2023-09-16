<?php

namespace Nsv\Util\Pdf;

/**
 * Floating text that automatically wraps when the margin is reached.
 */
class Text extends Element {

  function __construct(private $text) {}

  protected function renderWithStyles(Pdf $pdf) {
    $pdf->write($pdf->lineHeight, $this->text);
  }
}
