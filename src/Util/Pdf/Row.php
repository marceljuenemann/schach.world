<?php

namespace Nsv\Util\Pdf;

/**
 * A row of Cells. The Y position will automatically be set to the next row / line
 * after rendering.
 */
class Row implements Element {

  private $cells = [];

  public function addCell(Cell $cell) {
    $this->cells[] = $cell;
  }

  /**
   * Cells with no width set will be assigned a width such that
   * all available horizontal space is distributed equally to the cells.
   */
  public function layout(Pdf $pdf) {
    $availableWidth = $pdf->rMargin - $pdf->x;
    $cellsToGrow = [];
    foreach ($this->cells as $cell) {
      if ($cell->width) {
        $availableWidth -= $cell->width;
      } else {
        $cellsToGrow[] = $cell;
      }
    }

    $width = (float) $availableWidth / count($cellsToGrow);
    foreach ($cellsToGrow as $cell) {
      $cell->width = $width;
    }
  }

  public function render(Pdf $pdf) {
    $y = $pdf->y;
    $maxY = $y;
    foreach ($this->cells as $cell) {
      $pdf->y = $y;
      $cell->render($pdf);
      $maxY = max($pdf->y, $maxY);
    }
    $pdf->SetXY($pdf->lMargin, $maxY);
  }
}
