<?php

namespace Nsv\Util\Pdf;

// TODO: Seems overengineered, integrate into Table
class TableRow {

  public function __construct(public array $cells) {
    // TODO: verify cell type?
  }
  
  public function render(Pdf $pdf, array $columnWidths) {
    $prevL = $pdf->GetLeftMargin();
    $prevR = $pdf->GetRightMargin();
    $prevY = $pdf->GetY();

    $maxY = $prevY;  // Keep track of the heighest column.
    $column = 0;  

    foreach ($this->cells as $cell) {
      $width = $columnWidths[$column];
      if (!$width) continue;

      // Use margins to restrict rendering to desired width.
      $pdf->SetLeftMargin($pdf->GetX());
      $pdf->SetRightMargin($pdf->pageWidth() - ($pdf->GetX() + $width));
      $cell->render($pdf);  // TODO: support array? Or just use div

      if ($pdf->GetY() > $maxY) $maxY = $pdf->GetY();
      $pdf->SetXY($pdf->GetLeftMargin() + $width, $prevY);
      $column++;  // TODO: colspan
    }

    // Reset for next row.
    $pdf->SetLeftMargin($prevL);
    $pdf->SetRightMargin($prevR);
    $pdf->SetXY($prevL, $maxY + .3 );
  }

//      $pdf->Write($pdf->lineHeight, print_r($width, true));

}
