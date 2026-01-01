<?php

namespace Nsv\Util\Pdf;

/**
 * Floating text that automatically wraps when the margin is reached.
 */
class Text implements Element {

  function __construct(public readonly string $text) {}

  public function render(Pdf $pdf) {
    $pdf->write($pdf->lineHeight, $this->text);
    $pdf->Ln();
  }
}
