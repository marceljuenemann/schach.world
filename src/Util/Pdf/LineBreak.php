<?php

namespace Nsv\Util\Pdf;

class LineBreak extends Element {

  public function render(Pdf $pdf) {
    $pdf->Ln();
  }
}
