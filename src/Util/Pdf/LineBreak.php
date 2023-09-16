<?php

namespace Nsv\Util\Pdf;

// TODO: unnecessary?
class LineBreak extends Element {

  protected function renderWithStyles(Pdf $pdf) {
    $pdf->Ln();
  }
}
