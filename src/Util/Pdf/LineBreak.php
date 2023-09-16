<?php

namespace Nsv\Util\Pdf;

// TODO: rename to padding, use parameter.
class LineBreak extends Element {

  protected function renderWithStyles(Pdf $pdf) {
    $pdf->Ln();
  }
}
