<?php

namespace Nsv\Util\Pdf;

class TableRow {

  public function __construct(private array $cells) {
    // TODO: verify cell type?
  }
  
  public function render(Pdf $pdf, array $columnWidths) {
    $prevL = $pdf->GetLeftMargin();
    $prevR = $pdf->GetRightMargin();

    $column = 0;
    foreach ($this->cells as $cell) {
      $width = $columnWidths[$column];
      $pdf->SetLeftMargin($pdf->GetX());
      $pdf->SetRightMargin($pdf->pageWidth() - ($pdf->GetX() + $width));
      $cell->render($pdf);  // TODO: support array
    }

    $pdf->SetLeftMargin($prevL);
    $pdf->SetRightMargin($prevR);
    $pdf->Ln();  // TODO: dynmaic height.
  }
}
